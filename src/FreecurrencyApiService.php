<?php

declare(strict_types=1);

namespace Peso\Services;

use Override;
use Peso\Core\Exceptions\RequestNotSupportedException;
use Peso\Core\Requests\CurrentExchangeRateRequest;
use Peso\Core\Requests\HistoricalExchangeRateRequest;
use Peso\Core\Responses\ErrorResponse;
use Peso\Core\Responses\ExchangeRateResponse;
use Peso\Core\Services\PesoServiceInterface;
use Peso\Core\Services\SDK\Cache\NullCache;
use Peso\Core\Services\SDK\HTTP\DiscoveredHttpClient;
use Peso\Core\Services\SDK\HTTP\DiscoveredRequestFactory;
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

        $url = self::LATEST_ENDPOINT . http_build_query($query, encoding_type: PHP_QUERY_RFC3986);
    }

    #[Override]
    public function supports(object $request): bool
    {
        return $request instanceof CurrentExchangeRateRequest || $request instanceof HistoricalExchangeRateRequest;
    }
}
