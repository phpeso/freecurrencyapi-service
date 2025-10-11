<?php

/**
 * @copyright 2025 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Peso\Services\FreecurrencyApiService;

use Exception;
use Peso\Core\Exceptions\RuntimeException;

final class RateExceededException extends Exception implements RuntimeException
{
}
