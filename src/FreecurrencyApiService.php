<?php

declare(strict_types=1);

namespace Peso\Services;

use Peso\Core\Requests\CurrentExchangeRateRequest;
use Peso\Core\Requests\HistoricalExchangeRateRequest;
use Peso\Core\Responses\ConversionResponse;
use Peso\Core\Responses\ErrorResponse;
use Peso\Core\Responses\ExchangeRateResponse;
use Peso\Core\Services\PesoServiceInterface;

final readonly class FreecurrencyApiService implements PesoServiceInterface
{
    public function send(object $request): ExchangeRateResponse|ConversionResponse|ErrorResponse
    {
        // TODO: Implement send() method.
    }

    public function supports(object $request): bool
    {
        return $request instanceof CurrentExchangeRateRequest || $request instanceof HistoricalExchangeRateRequest;
    }
}
