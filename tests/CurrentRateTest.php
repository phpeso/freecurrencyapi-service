<?php

namespace Peso\Services\Tests;

use Arokettu\Date\Date;
use Peso\Core\Exceptions\ExchangeRateNotFoundException;
use Peso\Core\Requests\CurrentExchangeRateRequest;
use Peso\Core\Responses\ErrorResponse;
use Peso\Core\Responses\ExchangeRateResponse;
use Peso\Services\FreecurrencyApiService;
use Peso\Services\Tests\Helpers\MockClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class CurrentRateTest extends TestCase
{
    public function testRate(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $http = MockClient::get();

        $service = new FreecurrencyApiService('xxxfreexxx', cache: $cache, httpClient: $http);
        $today = Date::today()->toString();

        $response = $service->send(new CurrentExchangeRateRequest('EUR', 'USD'));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('1.1616963437', $response->rate->value);
        self::assertEquals($today, $response->date->toString());

        $response = $service->send(new CurrentExchangeRateRequest('USD', 'RUB'));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('77.9892830271', $response->rate->value);
        self::assertEquals($today, $response->date->toString());

        $response = $service->send(new CurrentExchangeRateRequest('RUB', 'JPY'));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('1.9033406028', $response->rate->value);
        self::assertEquals($today, $response->date->toString());

        $response = $service->send(new CurrentExchangeRateRequest('EUR', 'JPY')); // cached
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('172.4424015411', $response->rate->value);
        self::assertEquals($today, $response->date->toString());

        self::assertCount(3, $http->getRequests()); // subsequent requests are cached
    }

    public function testRateWithSymbols(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $http = MockClient::get();

        $service = new FreecurrencyApiService('xxxfreexxx', symbols: [
            'EUR', 'USD',
        ], cache: $cache, httpClient: $http);
        $today = Date::today()->toString();

        $response = $service->send(new CurrentExchangeRateRequest('EUR', 'USD'));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('1.1616963437', $response->rate->value);
        self::assertEquals($today, $response->date->toString());

        $response = $service->send(new CurrentExchangeRateRequest('USD', 'EUR'));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('0.8608101467', $response->rate->value);
        self::assertEquals($today, $response->date->toString());

        // any to symbols is ok
        $response = $service->send(new CurrentExchangeRateRequest('RUB', 'EUR'));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('0.011037544', $response->rate->value);
        self::assertEquals($today, $response->date->toString());

        // symbols to missing is not OK
        $response = $service->send(new CurrentExchangeRateRequest('EUR', 'RUB'));
        self::assertInstanceOf(ErrorResponse::class, $response);
        self::assertInstanceOf(ExchangeRateNotFoundException::class, $response->exception);
        self::assertEquals('Unable to find exchange rate for EUR/RUB', $response->exception->getMessage());
    }
}
