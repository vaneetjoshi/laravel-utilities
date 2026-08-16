<?php

namespace Vaneetjoshi\LaravelUtilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Format Facade
 * 
 * @method static string symbol(?string $currencyCode = null)
 * @method static string currency(mixed $amount, ?int $decimals = null, ?string $decimalSeparator = null, ?string $thousandSeparator = null, ?string $currencyCode = null)
 * @method static string date(mixed $date, ?string $format = null)
 *
 * @see \Vaneetjoshi\LaravelUtilities\Services\FormattingService
 */
class Format extends Facade
{
    /**
     * Get container accessor.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'utilities.formatting';
    }
}