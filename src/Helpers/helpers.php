<?php

/**
 * Global Support Engine Helper Functions
 *
 * Why we need this file:
 * This file exposes procedural shortcut functions for the three core pillars of the Support Engine:
 * Options Storage, Action/Filter Hooks, and Currency/Date Formatting. By globally autoloading
 * these helpers, developers can interact with underlying service singletons inside Blade views,
 * controllers, models, and console commands without manually resolving interfaces from the container.
 */

use Vaneetjoshi\LaravelUtilities\Services\FormattingService;
use Vaneetjoshi\LaravelUtilities\Services\HookService;
use Vaneetjoshi\LaravelUtilities\Services\OptionsService;
use Vaneetjoshi\LaravelUtilities\Settings\Enums\InputType;
use Vaneetjoshi\LaravelUtilities\Settings\SettingsRegistry;

// =========================================================================
// OPTIONS MANAGEMENT HELPERS
// =========================================================================

if (!function_exists('options')) {
    /**
     * Get the OptionsService singleton instance from the Laravel Service Container.
     *
     * Why we need this:
     * Provides direct object-oriented access to the underlying options service when
     * method chaining or advanced driver inspection is required.
     *
     * @return OptionsService The resolved OptionsService instance.
     */
    function options(): OptionsService
    {
        return app('utilities.options');
    }
}

if (!function_exists('getOption')) {
    /**
     * Retrieve an option value by its unique key identifier from the active Options Store.
     *
     * Why we need this:
     * Allows seamless retrieval of global or tenant-scoped settings. Automatically decodes
     * JSON structures if the stored value is an array or object.
     *
     * @param string $key The unique option identifier (e.g., 'site_name', 'default_currency').
     * @param mixed $default The default fallback value to return if the key does not exist in storage.
     * @return mixed The stored option value (deserialized if JSON), or the default fallback.
     */
    function getOption(string $key, mixed $default = null): mixed
    {
        return options()->get($key, $default);
    }
}

if (!function_exists('setOption')) {
    /**
     * Store or update a key-value option in the active Options Store.
     *
     * Why we need this:
     * Replaces legacy saveOption() with an intuitive syntax matching Laravel's config()->set()
     * and Cache::put(). Arrays and objects are automatically JSON-serialized prior to database storage.
     *
     * @param string $key The unique option identifier (e.g., 'maintenance_mode').
     * @param mixed $value The value to persist. Can be a primitive, array, or serializable object.
     * @return mixed The saved value exactly as passed into the method.
     */
    function setOption(string $key, mixed $value): mixed
    {
        return options()->set($key, $value);
    }
}

if (!function_exists('saveOption')) {
    /**
     * Backwards compatibility alias for setOption().
     *
     * Why we need this:
     * Ensures legacy modules and controllers using saveOption() continue to function
     * without breaking changes while transitioning to the new setOption() API.
     *
     * @param string $key The unique option identifier.
     * @param mixed $value The value to persist.
     * @return mixed The saved value.
     */
    function saveOption(string $key, mixed $value): mixed
    {
        return options()->set($key, $value);
    }
}

if (!function_exists('setManyOptions')) {
    /**
     * Store multiple key-value options in a single batch operation.
     *
     * Why we need this:
     * Highly optimized for administrative settings forms where dozens of configuration
     * fields are submitted simultaneously. Replaces legacy bulkSaveOptions().
     *
     * @param array<string, mixed> $options An associative array of key-value pairs to store.
     * @return void
     */
    function setManyOptions(array $options): void
    {
        options()->setMany($options);
    }
}

if (!function_exists('deleteOption')) {
    /**
     * Delete an option from storage by its unique key identifier.
     *
     * @param string $key The unique option identifier to remove from storage and runtime memory.
     * @return bool True if the option existed and was successfully deleted, false otherwise.
     */
    function deleteOption(string $key): bool
    {
        return options()->delete($key);
    }
}

if (!function_exists('flushOptionCache')) {
    /**
     * Purge all runtime memory caches inside the active Options Store.
     *
     * Why we need this:
     * Essential for persistent application servers (Laravel Octane, RoadRunner, Swoole)
     * and multi-tenant database switching loops to prevent reading stale options from previous states.
     *
     * @return void
     */
    function flushOptionCache(): void
    {
        options()->flushCache();
    }
}

// =========================================================================
// SMART SETTINGS HELPER
// =========================================================================

if (!function_exists('setting')) {
    /**
     * Retrieve a setting value, falling back to the schema-defined default automatically.
     *
     * @param string $key The unique field identifier.
     * @param mixed $fallback Optional absolute fallback if neither DB nor Schema has a value.
     * @return mixed
     */
    function setting(string $key, mixed $fallback = null): mixed
    {
        $registry = app(SettingsRegistry::class);
        
        // 1. Check the database (which utilizes our Tenant-Aware Cache)
        $dbValue = getOption($key);
        
        // 2. Resolve final value (fallback to Schema default if missing in DB)
        $value = $dbValue !== null ? $dbValue : $registry->getDefault($key, $fallback);

        // 3. Strictly cast checkboxes to booleans so you get true/false instead of "0"/"1"
        if ($registry->getFieldType($key) === InputType::CHECKBOX->value) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }
}

if (!function_exists('settings')) {
    /**
     * Just Plural version of setting
     *
     * @param string $key The unique field identifier.
     * @param mixed $fallback Optional absolute fallback if neither DB nor Schema has a value.
     * @return mixed
     */
    function settings(string $key, mixed $fallback = null): mixed
    {
        return setting($key, $fallback);
    }
}

// =========================================================================
// ACTION, FILTER & VIEW HOOK HELPERS
// =========================================================================

if (!function_exists('hooks')) {
    /**
     * Get the HookService singleton instance from the Laravel Service Container.
     *
     * @return HookService The resolved HookService instance.
     */
    function hooks(): HookService
    {
        return app('utilities.hooks');
    }
}

if (!function_exists('addAction')) {
    /**
     * Register a callback function to be executed when an action hook is triggered.
     *
     * Why we need this:
     * Enables decoupled modules and plugins to listen for application lifecycle events
     * (e.g., 'user.created', 'order.paid') without modifying core controller logic.
     *
     * @param string $hook The name of the action hook to listen to.
     * @param callable|string|array<int, string|object> $callback The callable, closure, or 'Class@method' string to execute.
     * @param int $priority Execution priority. Lower integers execute first (default is 10).
     * @return void
     * @throws \InvalidArgumentException If the provided callback is not callable or a valid class method.
     */
    function addAction(string $hook, callable|string|array $callback, int $priority = 10): void
    {
        hooks()->addAction($hook, $callback, $priority);
    }
}

if (!function_exists('removeAction')) {
    /**
     * Remove a previously registered callback from an action hook.
     *
     * Why we need this:
     * Allows runtime overrides or feature toggles to cleanly disable specific modular listeners
     * without rebuilding the entire hook registry.
     *
     * @param string $hook The name of the action hook.
     * @param callable|string|array<int, string|object> $callback The exact callback closure, string, or array to unregister.
     * @param int|null $priority Optional specific execution priority to check. If null, searches all priorities.
     * @return bool True if the callback was found and removed, false otherwise.
     */
    function removeAction(string $hook, callable|string|array $callback, ?int $priority = null): bool
    {
        return hooks()->removeAction($hook, $callback, $priority);
    }
}

if (!function_exists('doAction')) {
    /**
     * Trigger an action hook and execute all registered callbacks using PHP 8 variadic unpacking.
     *
     * @param string $hook The name of the action hook to execute.
     * @param mixed ...$args Arbitrary context arguments passed directly to all registered callbacks.
     * @return void
     */
    function doAction(string $hook, mixed ...$args): void
    {
        hooks()->doAction($hook, ...$args);
    }
}

if (!function_exists('addFilter')) {
    /**
     * Register a callback function to modify a variable or data payload when a filter hook is applied.
     *
     * Why we need this:
     * Essential for extensibility, allowing modules to mutate strings, arrays, or query builder
     * instances (e.g., filtering commission calculation rates or altering navigation menus).
     *
     * @param string $hook The name of the filter hook.
     * @param callable|string|array<int, string|object> $callback The callback function responsible for modifying the value.
     * @param int $priority Execution priority (default is 10).
     * @return void
     * @throws \InvalidArgumentException If the provided callback is invalid.
     */
    function addFilter(string $hook, callable|string|array $callback, int $priority = 10): void
    {
        hooks()->addFilter($hook, $callback, $priority);
    }
}

if (!function_exists('removeFilter')) {
    /**
     * Remove a previously registered callback from a filter hook.
     *
     * @param string $hook The name of the filter hook.
     * @param callable|string|array<int, string|object> $callback The callback to unregister.
     * @param int|null $priority Optional specific priority to target.
     * @return bool True if the filter callback was successfully removed, false otherwise.
     */
    function removeFilter(string $hook, callable|string|array $callback, ?int $priority = null): bool
    {
        return hooks()->removeFilter($hook, $callback, $priority);
    }
}

if (!function_exists('applyFilters')) {
    /**
     * Pass a variable through all callback functions registered to a specific filter hook.
     *
     * @param string $hook The name of the filter hook.
     * @param mixed $value The initial variable or payload to be modified by registered filters.
     * @param mixed ...$args Additional context arguments passed to filter callbacks.
     * @return mixed The final mutated value after all registered callbacks have executed.
     */
    function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        return hooks()->applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * WordPress-style alias for applyFilters().
     *
     * Why we need this:
     * Provides ergonomic familiarity for developers transitioning from WordPress ecosystem paradigms.
     *
     * @param string $hook The name of the filter hook.
     * @param mixed $value The initial value to filter.
     * @param mixed ...$args Additional context arguments.
     * @return mixed The filtered value.
     */
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        return hooks()->applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('addView')) {
    /**
     * Register a view rendering callback to an HTML layout hook slot.
     *
     * Why we need this:
     * Allows independent modules to inject custom UI components (such as notification bells,
     * alert banners, or custom JavaScript scripts) directly into frontend theme layouts.
     *
     * @param string $hook The layout slot identifier (e.g., 'navbar.end', 'footer.scripts').
     * @param callable|string|array<int, string|object> $callback Closure or callable returning an HTML string or rendered View.
     * @param int $priority Execution priority (default is 10).
     * @return void
     * @throws \InvalidArgumentException If the provided callback is not callable.
     */
    function addView(string $hook, callable|string|array $callback, int $priority = 10): void
    {
        hooks()->addView($hook, $callback, $priority);
    }
}

if (!function_exists('removeView')) {
    /**
     * Remove a previously registered view callback from a UI layout hook slot.
     *
     * @param string $hook The layout slot identifier.
     * @param callable|string|array<int, string|object> $callback The callback to remove.
     * @param int|null $priority Optional priority level to check.
     * @return bool True if the view callback was removed, false otherwise.
     */
    function removeView(string $hook, callable|string|array $callback, ?int $priority = null): bool
    {
        return hooks()->removeView($hook, $callback, $priority);
    }
}

if (!function_exists('renderViewHook')) {
    /**
     * Execute all view callbacks registered to a slot and return concatenated HTML string.
     * Features UI-safe exception isolation to prevent broken widgets from crashing the surrounding layout.
     *
     * @param string $hook The layout slot identifier (e.g., 'sidebar.top').
     * @param mixed ...$args Arbitrary context variables passed to the view callbacks.
     * @return string The concatenated HTML string produced by all registered callbacks.
     */
    function renderViewHook(string $hook, mixed ...$args): string
    {
        return hooks()->render($hook, ...$args);
    }
}

if (!function_exists('renderView')) {
    /**
     * Execute all view callbacks registered to a slot and return concatenated HTML string.
     * Features UI-safe exception isolation to prevent broken widgets from crashing the surrounding layout.
     *
     * @param string $hook The layout slot identifier (e.g., 'sidebar.top').
     * @param mixed ...$args Arbitrary context variables passed to the view callbacks.
     * @return string The concatenated HTML string produced by all registered callbacks.
     */
    function renderView(string $hook, mixed ...$args): string
    {
        return hooks()->render($hook, ...$args);
    }
}

// =========================================================================
// CURRENCY & DATE FORMATTING HELPERS
// =========================================================================

if (!function_exists('currency_symbol')) {
    /**
     * Resolve the active currency symbol based on stored database options or an explicit ISO code.
     *
     * Why we need this:
     * Essential for form inputs and pricing UI elements where the raw currency symbol
     * (e.g., '$', '₹', '€') must be rendered adjacent to an unformatted numerical text field.
     *
     * @param string|null $currencyCode Optional explicit ISO currency code override (e.g., 'INR', 'EUR', 'USDT').
     * @return string The resolved currency symbol, or an empty string if option is set to 'BLANK'.
     */
    function currency_symbol(?string $currencyCode = null): string
    {
        return app(FormattingService::class)->symbol($currencyCode);
    }
}

if (!function_exists('currency_format')) {
    /**
     * Format a numeric amount into a standardized currency string with symbol.
     *
     * Why we need this:
     * Ensures uniform financial display across invoices, checkout carts, MLM commission ledgers,
     * and reporting dashboards. Automatically trims leading whitespace if symbol is blank.
     *
     * @param mixed $amount The numeric value to format (integer, float, or numeric string).
     * @param int|null $decimals Optional decimal precision override (defaults to config('utilities.currency.decimals', 2)).
     * @param string|null $decimalSeparator Optional decimal character override (defaults to '.').
     * @param string|null $thousandSeparator Optional thousand separator override (defaults to ',').
     * @param string|null $currencyCode Optional explicit ISO currency code override for this specific call.
     * @return string Formatted string with symbol (e.g., '$ 1,250.00', '₹ 500.00', or '150.00' if BLANK).
     */
    function currency_format(
        mixed $amount,
        ?int $decimals = null,
        ?string $decimalSeparator = null,
        ?string $thousandSeparator = null,
        ?string $currencyCode = null
    ): string {
        return app(FormattingService::class)->currency($amount, $decimals, $decimalSeparator, $thousandSeparator, $currencyCode);
    }
}

if (!function_exists('numberToWords')) {
    /**
     * Convert a numeric amount into capitalized English words (e.g., for Invoice Totals).
     * Automatically attempts to use PHP's Native NumberFormatter, with a robust custom fallback.
     *
     * @param float|int $number The number to convert to words.
     * @return string The capitalized text representation (e.g., "Fifteen Thousand").
     */
    function numberToWords(float|int $number): string
    {
        $number = (int) floor($number); // We strictly handle the whole number. Decimals should be managed separately if needed.

        if ($number === 0) {
            return 'Zero';
        }

        // Try to use the native PHP intl extension for perfect grammar localization
        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
            return ucwords($formatter->format($number));
        }

        // Lightweight Fallback logic for basic servers missing the PHP intl extension
        $dictionary  = [
            0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 
            7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve', 
            13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 
            18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'fourty', 
            50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety',
            100 => 'hundred', 1000 => 'thousand', 1000000 => 'million', 1000000000 => 'billion'
        ];

        if ($number < 21) {
            return ucwords($dictionary[$number]);
        }

        if ($number < 100) {
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= '-' . $dictionary[$units];
            }
            return ucwords($string);
        }

        if ($number < 1000) {
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= ' and ' . numberToWords($remainder);
            }
            return ucwords($string);
        }

        $baseUnit = pow(1000, floor(log($number, 1000)));
        $numBaseUnits = (int) ($number / $baseUnit);
        $remainder = $number % $baseUnit;
        $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
        if ($remainder) {
            $string .= $remainder < 100 ? ' and ' : ', ';
            $string .= numberToWords($remainder);
        }

        return ucwords($string);
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format a date string, UNIX timestamp, or Carbon instance into a standardized readable string.
     *
     * Why we need this:
     * Guarantees that timestamps displayed across user profiles, order histories, and audit logs
     * consistently follow the system-wide date structure defined in config/utilities.php.
     *
     * @param mixed $date The input date (string, integer timestamp, DateTimeInterface, or Carbon instance).
     * @param string|null $format Optional PHP date format string override (defaults to config('utilities.date_format')).
     * @return string Formatted date string (e.g., '27 July 2026 08:43 AM'), or original string if parsing fails.
     */
    function formatDate(mixed $date, ?string $format = null): string
    {
        return app(FormattingService::class)->date($date, $format);
    }
}

// =========================================================================
// WIDGET HELPERS
// =========================================================================

if (!function_exists('widget')) {
    /**
     * Retrieve all active, sorted widgets for a specific zone.
     *
     * Why we need this:
     * Provides a clean, fluent helper for Blade templates to fetch widget data
     * without needing to use the fully qualified Facade namespace.
     *
     * @param string $zoneName The name of the widget zone (e.g., 'dashboard_top').
     * @return array<string, \Vaneetjoshi\LaravelUtilities\Widgets\DTOs\WidgetDTO>
     */
    function widget(string $zoneName): array
    {
        return \Vaneetjoshi\LaravelUtilities\Facades\Widgets::zone($zoneName)->get();
    }
}

// =========================================================================
// NAVBAR HELPERS
// =========================================================================

if (!function_exists('navbar')) {
    /**
     * Retrieve all active, sorted and nested navigation items for a specific zone.
     *
     * @param string $zoneName The name of the navbar zone (e.g., 'sidebar_main', 'top_nav').
     * @return array<string, \Vaneetjoshi\LaravelUtilities\Navbars\DTOs\NavbarItemDTO>
     */
    function navbar(string $zoneName): array
    {
        return \Vaneetjoshi\LaravelUtilities\Facades\Navbars::zone($zoneName)->get();
    }
}