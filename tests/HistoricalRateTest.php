<?php

declare(strict_types=1);

namespace Peso\Services\Tests;

use Arokettu\Date\Calendar;
use Arokettu\Date\Date;
use Peso\Core\Requests\CurrentExchangeRateRequest;
use Peso\Core\Requests\HistoricalExchangeRateRequest;
use Peso\Core\Responses\ExchangeRateResponse;
use Peso\Services\FreecurrencyApiService;
use Peso\Services\Tests\Helpers\MockClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class HistoricalRateTest extends TestCase
{
    public function testRate(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $http = MockClient::get();

        $service = new FreecurrencyApiService('xxxfreexxx', cache: $cache, httpClient: $http);
        $date = Calendar::parse('2025-06-13');

        $response = $service->send(new HistoricalExchangeRateRequest('EUR', 'USD', $date));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('1.1545609338', $response->rate->value);
        self::assertEquals('2025-06-13', $response->date->toString());

        $response = $service->send(new HistoricalExchangeRateRequest('USD', 'RUB', $date));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('79.6892896254', $response->rate->value);
        self::assertEquals('2025-06-13', $response->date->toString());

        $response = $service->send(new HistoricalExchangeRateRequest('RUB', 'JPY', $date));
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('1.809309539', $response->rate->value);
        self::assertEquals('2025-06-13', $response->date->toString());

        $response = $service->send(new HistoricalExchangeRateRequest('EUR', 'JPY', $date)); // cached
        self::assertInstanceOf(ExchangeRateResponse::class, $response);
        self::assertEquals('166.4675879133', $response->rate->value);
        self::assertEquals('2025-06-13', $response->date->toString());

        self::assertCount(3, $http->getRequests()); // subsequent requests are cached
    }
}
