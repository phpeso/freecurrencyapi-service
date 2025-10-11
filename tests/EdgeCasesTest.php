<?php

/**
 * @copyright 2025 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Peso\Services\Tests;

use Peso\Core\Exceptions\RequestNotSupportedException;
use Peso\Core\Requests\CurrentConversionRequest;
use Peso\Core\Requests\CurrentExchangeRateRequest;
use Peso\Core\Responses\ErrorResponse;
use Peso\Core\Services\SDK\Exceptions\HttpFailureException;
use Peso\Core\Types\Decimal;
use Peso\Services\FreecurrencyApiService;
use Peso\Services\Tests\Helpers\MockClient;
use PHPUnit\Framework\TestCase;

final class EdgeCasesTest extends TestCase
{
    public function testInvalidRequest(): void
    {
        $service = new FreecurrencyApiService('xxx');

        $response = $service->send(new CurrentConversionRequest(Decimal::init('100'), 'TRY', 'PHP'));
        self::assertInstanceOf(ErrorResponse::class, $response);
        self::assertInstanceOf(RequestNotSupportedException::class, $response->exception);
        self::assertEquals(
            'Unsupported request type: "Peso\Core\Requests\CurrentConversionRequest"',
            $response->exception->getMessage(),
        );
    }

    public function testRateExceeded(): void
    {
        $http = MockClient::get();
        $service = new FreecurrencyApiService('rate_exceeded', httpClient: $http);

        self::expectException(FreecurrencyApiService\RateExceededException::class);
        self::expectExceptionMessage('Rate exceeded');
        $service->send(new CurrentExchangeRateRequest('TRY', 'PHP'));
    }

    public function testInvalidKey(): void
    {
        $http = MockClient::get();
        $service = new FreecurrencyApiService('invalid', httpClient: $http);

        self::expectException(HttpFailureException::class);
        self::expectExceptionMessage('Invalid authentication credentials');
        $service->send(new CurrentExchangeRateRequest('TRY', 'PHP'));
    }
}
