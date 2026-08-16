<?php

namespace Vaneetjoshi\LaravelUtilities\Services;

use Carbon\Carbon;

/**
 * Class FormattingService
 * 
 * Why we need this file:
 * Provides centralized, ecosystem-wide currency and date formatting utilities.
 * By decoupling formatting logic from individual views and controllers, we ensure
 * identical financial display and timestamp rendering across all tenant storefronts,
 * admin dashboards, and automated email notifications.
 */
class FormattingService
{
    /**
     * Resolve the active currency symbol based on database options or explicit override.
     *
     * Why we separate this into its own method:
     * Frontend forms, input group addons, and invoice headers frequently require just the
     * raw currency symbol without the formatted numerical amount (e.g., displaying "$").
     *
     * @param string|null $currencyCode Optional explicit ISO currency code override (e.g., 'INR', 'EUR').
     * @return string The resolved currency symbol, or an empty string if set to 'BLANK'.
     */
    public function symbol(?string $currencyCode = null): string
    {
        // 1. Resolve currency code from parameter, database option, or config fallback
        if ($currencyCode === null) {
            $currencyCode = function_exists('getOption') 
                ? getOption('currency', config('utilities.currency.default', 'USD')) 
                : config('utilities.currency.default', 'USD');
        }

        // Normalize string to uppercase for clean matching
        $code = strtoupper(trim((string) $currencyCode));

        // 2. Match against supported ISO codes, crypto identifiers, and custom rules
        return match ($code) {
            'BLANK', 'NONE', '' => '',
            'CUSTOM' => function_exists('getOption') ? (string) getOption('customCurrencySymbol', '$') : '$',
            'USD', 'USDT', 'CAD', 'AUD', 'NZD', 'SGD', 'HKD', 'MXN' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            'JPY', 'CNY' => '¥',
            'CHF' => 'CHF',
            'BRL' => 'R$',
            'ZAR' => 'R',
            'AED' => 'د.إ',
            'SAR' => '﷼',
            'KRW' => '₩',
            'TRY' => '₺',
            'RUB' => '₽',
            'THB' => '฿',
            'IDR' => 'Rp',
            'MYR' => 'RM',
            'PHP' => '₱',
            'VND' => '₫',
            default => config('utilities.currency.symbol', '$'),
        };
    }

    /**
     * Format a numeric amount into a standardized currency string with symbol.
     *
     * Why we use trim():
     * If the resolved symbol is empty (e.g., when option is set to 'BLANK'), standard
     * concatenation leaves a leading space (" 500.00"). Trimming ensures clean output.
     *
     * @param mixed $amount The numeric value to format (integer, float, or numeric string).
     * @param int|null $decimals Number of decimal places (defaults to config('utilities.currency.decimals', 2)).
     * @param string|null $decimalSeparator Character separating decimals (defaults to '.').
     * @param string|null $thousandSeparator Character separating thousands (defaults to ',').
     * @param string|null $currencyCode Optional explicit currency code override for this specific call.
     * @return string Formatted string with symbol (e.g., '$ 1,250.00', '₹ 500.00', or '500.00' if BLANK).
     */
    public function currency(
        mixed $amount, 
        ?int $decimals = null, 
        ?string $decimalSeparator = null, 
        ?string $thousandSeparator = null,
        ?string $currencyCode = null
    ): string {
        // Safely cast input amount to float, defaulting to 0.0 for invalid strings
        $numericAmount = is_numeric($amount) ? (float) $amount : 0.0;

        // Resolve formatting parameters from overrides or system configuration
        $decimals = $decimals ?? (int) config('utilities.currency.decimals', 2);
        $decimalSeparator = $decimalSeparator ?? config('utilities.currency.decimal_separator', '.');
        $thousandSeparator = $thousandSeparator ?? config('utilities.currency.thousand_separator', ',');

        // Resolve the symbol using our dynamic matching engine
        $symbol = $this->symbol($currencyCode);

        // Format the raw numerical value
        $formattedNumber = number_format($numericAmount, $decimals, $decimalSeparator, $thousandSeparator);

        // Combine symbol and number, trimming awkward whitespace if symbol is blank
        return trim("{$symbol} {$formattedNumber}");
    }

    /**
     * Format a date string, timestamp, or Carbon instance into a standardized readable string.
     *
     * Why we need this:
     * Guarantees that timestamps across user profiles, commission ledgers, and audit logs
     * follow the exact formatting structure defined in config/utilities.php.
     *
     * @param mixed $date The date input (string, integer timestamp, DateTimeInterface, or Carbon).
     * @param string|null $format The target PHP date format string (defaults to config('utilities.date_format')).
     * @return string Formatted date string (e.g., '27 July 2026 08:43 AM'), or original string if parsing fails.
     */
    public function date(mixed $date, ?string $format = null): string
    {
        if (empty($date)) {
            return '';
        }

        $targetFormat = $format ?? config('utilities.date_format', 'd F Y H:i A');

        try {
            if ($date instanceof Carbon || $date instanceof \DateTimeInterface) {
                return Carbon::instance($date)->format($targetFormat);
            }

            if (is_numeric($date)) {
                return Carbon::createFromTimestamp((int) $date)->format($targetFormat);
            }

            return Carbon::parse((string) $date)->format($targetFormat);
        } catch (\Throwable $e) {
            // Return raw string fallback if input cannot be parsed as a valid timestamp
            return (string) $date;
        }
    }
}