<?php

declare(strict_types=1);

namespace Peso\Services;

use Arokettu\Date\Date;
use DateInterval;
use Error;
use Override;
use Peso\Core\Exceptions\ExchangeRateNotFoundException;
use Peso\Core\Exceptions\RequestNotSupportedException;
use Peso\Core\Requests\CurrentExchangeRateRequest;
use Peso\Core\Requests\HistoricalExchangeRateRequest;
use Peso\Core\Responses\ErrorResponse;
use Peso\Core\Responses\ExchangeRateResponse;
use Peso\Core\Services\PesoServiceInterface;
use Peso\Core\Services\SDK\Cache\NullCache;
use Peso\Core\Services\SDK\Exceptions\HttpFailureException;
use Peso\Core\Services\SDK\HTTP\DiscoveredHttpClient;
use Peso\Core\Services\SDK\HTTP\DiscoveredRequestFactory;
use Peso\Core\Services\SDK\HTTP\UserAgentHelper;
use Peso\Core\Types\Decimal;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\SimpleCache\CacheInterface;

final readonly class FreecurrencyApiService implements PesoServiceInterface
{
    private const LATEST_ENDPOINT = 'https://api.freecurrencyapi.com/v1/latest?';
    private const HISTORICAL_ENDPOINT = 'https://api.freecurrencyapi.com/v1/historical?';

    public function __construct(
        private string $apiKey,
        private array|null $symbols = null,
        private CacheInterface $cache = new NullCache(),
        private DateInterval $ttl = new DateInterval('PT1H'),
        private ClientInterface $httpClient = new DiscoveredHttpClient(),
        private RequestFactoryInterface $requestFactory = new DiscoveredRequestFactory(),
    ) {
    }

    #[Override]
    public function send(object $request): ExchangeRateResponse|ErrorResponse
    {
        if ($request instanceof CurrentExchangeRateRequest) {
            return self::performCurrentRequest($request);
        }
        if ($request instanceof HistoricalExchangeRateRequest) {
            return self::performHistoricalRequest($request);
        }
        return new ErrorResponse(RequestNotSupportedException::fromRequest($request));
    }

    private function performCurrentRequest(CurrentExchangeRateRequest $request): ErrorResponse|ExchangeRateResponse
    {
        $query = [
            'apikey' => $this->apiKey,
            'base_currency' => $request->baseCurrency,
            'currencies' => $this->symbols === null ? null : implode(',', $this->symbols),
        ];

        $url = self::LATEST_ENDPOINT . http_build_query($query, encoding_type: PHP_QUERY_RFC3986);

        $rates = $this->retrieveRates($url);

        $rate = $rates[$request->quoteCurrency] ?? null;

        return $rate === null ?
            new ErrorResponse(ExchangeRateNotFoundException::fromRequest($request)) :
            new ExchangeRateResponse(Decimal::init($rate), Date::today());
    }

    private function performHistoricalRequest(
        HistoricalExchangeRateRequest $request,
    ): ErrorResponse|ExchangeRateResponse {
        $query = [
            'apikey' => $this->apiKey,
            'date' => $request->date->toString(),
            'base_currency' => $request->baseCurrency,
            'currencies' => $this->symbols === null ? null : implode(',', $this->symbols),
        ];

        $url = self::HISTORICAL_ENDPOINT . http_build_query($query, encoding_type: PHP_QUERY_RFC3986);

        $rates = $this->retrieveRates($url);

        $rate = $rates[$request->date->toString()][$request->quoteCurrency] ?? null;

        return $rate === null ?
            new ErrorResponse(ExchangeRateNotFoundException::fromRequest($request)) :
            new ExchangeRateResponse(Decimal::init($rate), $request->date);
    }

    private function retrieveRates(string $url): array|false
    {
        $cacheKey = 'peso|fca|' . hash('sha1', $url);

        $rates = $this->cache->get($cacheKey);

        if ($rates !== null) {
            return $rates;
        }

        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $request->withHeader('User-Agent', UserAgentHelper::buildUserAgentString(
            'FreecurrencyAPI',
            'peso/freecurrencyapi-service',
            $request->hasHeader('User-Agent') ? $request->getHeaderLine('User-Agent') : null,
        ));
        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw HttpFailureException::fromResponse($request, $response);
        }

        $rates = json_decode(
            (string)$response->getBody(),
            flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY,
        )['data'] ?? throw new Error('No rates in the response');

        $this->cache->set($cacheKey, $rates, $this->ttl);

        return $rates;
    }

    #[Override]
    public function supports(object $request): bool
    {
        return $request instanceof CurrentExchangeRateRequest || $request instanceof HistoricalExchangeRateRequest;
    }
}
