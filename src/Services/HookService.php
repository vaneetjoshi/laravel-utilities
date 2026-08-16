<?php

namespace Vaneetjoshi\LaravelUtilities\Services;

use Closure;
use InvalidArgumentException;
use Throwable;

/**
 * Class HookService
 * 
 * Why we need this file:
 * Implements an event-driven Action, Filter, and View rendering engine with strict
 * callable validation, deduplication, O(1) sorting flags, and inspection utilities.
 * Enables standalone packages to interact without direct class coupling.
 */
class HookService
{
    /**
     * Registered action callbacks grouped by hook name and priority.
     *
     * @var array<string, array<int, array<int, callable|string|array>>>
     */
    protected array $actions = [];

    /**
     * Registered filter callbacks grouped by hook name and priority.
     *
     * @var array<string, array<int, array<int, callable|string|array>>>
     */
    protected array $filters = [];

    /**
     * Registered view rendering callbacks grouped by hook name and priority.
     *
     * @var array<string, array<int, array<int, callable|string|array>>>
     */
    protected array $views = [];

    /**
     * Dirty flags tracking whether an action hook's priority array requires sorting.
     *
     * @var array<string, bool>
     */
    protected array $sortedActions = [];

    /**
     * Dirty flags tracking whether a filter hook's priority array requires sorting.
     *
     * @var array<string, bool>
     */
    protected array $sortedFilters = [];

    /**
     * Dirty flags tracking whether a view hook's priority array requires sorting.
     *
     * @var array<string, bool>
     */
    protected array $sortedViews = [];

    /**
     * Validate whether a callback is callable or a valid Laravel container string.
     *
     * @param mixed $callback The callback to validate.
     * @return void
     * @throws InvalidArgumentException If the callback is invalid.
     */
    protected function validateCallback(mixed $callback): void
    {
        if (is_callable($callback, true)) {
            // If it's an array like [UserService::class, 'method'], verify method exists if class is loaded
            if (is_array($callback) && count($callback) === 2 && is_string($callback[0])) {
                if (class_exists($callback[0]) && !method_exists($callback[0], $callback[1]) && !method_exists($callback[0], '__callStatic')) {
                    throw new InvalidArgumentException("Method [{$callback[1]}] does not exist on class [{$callback[0]}].");
                }
            }
            return;
        }

        // Allow Laravel container syntax: 'Class@method'
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback, 2);
            if (class_exists($class) && !method_exists($class, $method) && !method_exists($class, '__call')) {
                throw new InvalidArgumentException("Method [{$method}] does not exist on class [{$class}].");
            }
            return;
        }

        throw new InvalidArgumentException("The provided callback is not valid or callable.");
    }

    /**
     * Check if a callback is already registered to prevent duplicate execution.
     *
     * @param array<int, callable|string|array> $callbacks
     * @param mixed $newCallback
     * @return bool
     */
    protected function isDuplicate(array $callbacks, mixed $newCallback): bool
    {
        foreach ($callbacks as $existing) {
            if ($existing === $newCallback) {
                return true;
            }
            // Compare closure serialization or object identifiers if both are closures
            if ($existing instanceof Closure && $newCallback instanceof Closure) {
                if (spl_object_id($existing) === spl_object_id($newCallback)) {
                    return true;
                }
            }
        }
        return false;
    }

    // =========================================================================
    // ACTION HOOKS
    // =========================================================================

    /**
     * Register a callback function to an action hook.
     *
     * @param string $hook The action name.
     * @param callable|string|array $callback The callback to execute.
     * @param int $priority Execution priority (lower executes first, default 10).
     * @return void
     * @throws InvalidArgumentException
     */
    public function addAction(string $hook, callable|string|array $callback, int $priority = 10): void
    {
        $this->validateCallback($callback);

        if (!isset($this->actions[$hook][$priority])) {
            $this->actions[$hook][$priority] = [];
        }

        if ($this->isDuplicate($this->actions[$hook][$priority], $callback)) {
            return;
        }

        $this->actions[$hook][$priority][] = $callback;
        $this->sortedActions[$hook] = false;
    }

    /**
     * Remove a registered callback from an action hook.
     *
     * @param string $hook The action name.
     * @param callable|string|array $callback The callback to remove.
     * @param int|null $priority Optional specific priority to check.
     * @return bool True if removed, false otherwise.
     */
    public function removeAction(string $hook, callable|string|array $callback, ?int $priority = null): bool
    {
        if (!isset($this->actions[$hook])) {
            return false;
        }

        $removed = false;
        $priorities = $priority !== null ? [$priority] : array_keys($this->actions[$hook]);

        foreach ($priorities as $p) {
            if (!isset($this->actions[$hook][$p])) {
                continue;
            }

            foreach ($this->actions[$hook][$p] as $index => $registered) {
                if ($registered === $callback || ($registered instanceof Closure && $callback instanceof Closure && spl_object_id($registered) === spl_object_id($callback))) {
                    unset($this->actions[$hook][$p][$index]);
                    $this->actions[$hook][$p] = array_values($this->actions[$hook][$p]);
                    $removed = true;
                }
            }

            if (empty($this->actions[$hook][$p])) {
                unset($this->actions[$hook][$p]);
            }
        }

        if (empty($this->actions[$hook])) {
            unset($this->actions[$hook], $this->sortedActions[$hook]);
        }

        return $removed;
    }

    /**
     * Execute all callbacks registered to an action hook using PHP 8 variadic unpacking.
     *
     * @param string $hook The action name.
     * @param mixed ...$args Arguments passed to callbacks.
     * @return void
     */
    public function doAction(string $hook, mixed ...$args): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }

        // Sort priority array ONLY when new callbacks have been added
        if (($this->sortedActions[$hook] ?? false) === false) {
            ksort($this->actions[$hook]);
            $this->sortedActions[$hook] = true;
        }

        foreach ($this->actions[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_string($callback) && str_contains($callback, '@')) {
                    app()->call($callback, $args);
                } else {
                    $callback(...$args);
                }
            }
        }
    }

    // =========================================================================
    // FILTER HOOKS
    // =========================================================================

    /**
     * Register a callback function to a filter hook.
     *
     * @param string $hook The filter name.
     * @param callable|string|array $callback The filter callback.
     * @param int $priority Execution priority (default 10).
     * @return void
     * @throws InvalidArgumentException
     */
    public function addFilter(string $hook, callable|string|array $callback, int $priority = 10): void
    {
        $this->validateCallback($callback);

        if (!isset($this->filters[$hook][$priority])) {
            $this->filters[$hook][$priority] = [];
        }

        if ($this->isDuplicate($this->filters[$hook][$priority], $callback)) {
            return;
        }

        $this->filters[$hook][$priority][] = $callback;
        $this->sortedFilters[$hook] = false;
    }

    /**
     * Remove a registered callback from a filter hook.
     *
     * @param string $hook The filter name.
     * @param callable|string|array $callback The callback to remove.
     * @param int|null $priority Optional specific priority.
     * @return bool
     */
    public function removeFilter(string $hook, callable|string|array $callback, ?int $priority = null): bool
    {
        if (!isset($this->filters[$hook])) {
            return false;
        }

        $removed = false;
        $priorities = $priority !== null ? [$priority] : array_keys($this->filters[$hook]);

        foreach ($priorities as $p) {
            if (!isset($this->filters[$hook][$p])) {
                continue;
            }

            foreach ($this->filters[$hook][$p] as $index => $registered) {
                if ($registered === $callback || ($registered instanceof Closure && $callback instanceof Closure && spl_object_id($registered) === spl_object_id($callback))) {
                    unset($this->filters[$hook][$p][$index]);
                    $this->filters[$hook][$p] = array_values($this->filters[$hook][$p]);
                    $removed = true;
                }
            }

            if (empty($this->filters[$hook][$p])) {
                unset($this->filters[$hook][$p]);
            }
        }

        if (empty($this->filters[$hook])) {
            unset($this->filters[$hook], $this->sortedFilters[$hook]);
        }

        return $removed;
    }

    /**
     * Pass a value through all registered filter callbacks using native unpacking.
     *
     * @param string $hook The filter name.
     * @param mixed $value The value to filter.
     * @param mixed ...$args Additional context arguments.
     * @return mixed The filtered value.
     */
    public function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$hook])) {
            return $value;
        }

        if (($this->sortedFilters[$hook] ?? false) === false) {
            ksort($this->filters[$hook]);
            $this->sortedFilters[$hook] = true;
        }

        foreach ($this->filters[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_string($callback) && str_contains($callback, '@')) {
                    $value = app()->call($callback, array_merge([$value], $args));
                } else {
                    $value = $callback($value, ...$args);
                }
            }
        }

        return $value;
    }

    // =========================================================================
    // VIEW HOOKS (Resilient UI Rendering)
    // =========================================================================

    /**
     * Register a callback returning HTML/Blade string to a UI layout hook.
     *
     * @param string $hook The layout slot identifier (e.g., 'navbar.end').
     * @param callable|string|array $callback Closure returning HTML or View render.
     * @param int $priority Execution priority (default 10).
     * @return void
     * @throws InvalidArgumentException
     */
    public function addView(string $hook, callable|string|array $callback, int $priority = 10): void
    {
        $this->validateCallback($callback);

        if (!isset($this->views[$hook][$priority])) {
            $this->views[$hook][$priority] = [];
        }

        if ($this->isDuplicate($this->views[$hook][$priority], $callback)) {
            return;
        }

        $this->views[$hook][$priority][] = $callback;
        $this->sortedViews[$hook] = false;
    }

    /**
     * Remove a registered view callback from a UI hook.
     *
     * @param string $hook
     * @param callable|string|array $callback
     * @param int|null $priority
     * @return bool
     */
    public function removeView(string $hook, callable|string|array $callback, ?int $priority = null): bool
    {
        if (!isset($this->views[$hook])) {
            return false;
        }

        $removed = false;
        $priorities = $priority !== null ? [$priority] : array_keys($this->views[$hook]);

        foreach ($priorities as $p) {
            if (!isset($this->views[$hook][$p])) {
                continue;
            }

            foreach ($this->views[$hook][$p] as $index => $registered) {
                if ($registered === $callback || ($registered instanceof Closure && $callback instanceof Closure && spl_object_id($registered) === spl_object_id($callback))) {
                    unset($this->views[$hook][$p][$index]);
                    $this->views[$hook][$p] = array_values($this->views[$hook][$p]);
                    $removed = true;
                }
            }

            if (empty($this->views[$hook][$p])) {
                unset($this->views[$hook][$p]);
            }
        }

        if (empty($this->views[$hook])) {
            unset($this->views[$hook], $this->sortedViews[$hook]);
        }

        return $removed;
    }

    /**
     * Execute all view callbacks registered to a slot and return concatenated HTML.
     * Features UI-Safe exception isolation: logs errors without crashing the surrounding layout.
     *
     * @param string $hook The layout slot identifier.
     * @param mixed ...$args Context arguments passed to callbacks.
     * @return string Concatenated HTML string.
     */
    public function render(string $hook, mixed ...$args): string
    {
        if (!isset($this->views[$hook])) {
            return '';
        }

        if (($this->sortedViews[$hook] ?? false) === false) {
            ksort($this->views[$hook]);
            $this->sortedViews[$hook] = true;
        }

        $output = '';

        foreach ($this->views[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                try {
                    $result = is_string($callback) && str_contains($callback, '@') 
                        ? app()->call($callback, $args) 
                        : $callback(...$args);

                    if (is_string($result) || (is_object($result) && method_exists($result, '__toString'))) {
                        $output .= (string) $result;
                    }
                } catch (Throwable $e) {
                    // Isolate UI failures: log error and continue rendering remaining widgets
                    if (function_exists('logger')) {
                        logger()->error("View Hook [{$hook}] failed: " . $e->getMessage(), ['exception' => $e]);
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Backwards compatibility alias for render().
     *
     * @param string $hook
     * @param mixed ...$args
     * @return string
     */
    public function renderViewHook(string $hook, mixed ...$args): string
    {
        return $this->render($hook, ...$args);
    }

    // =========================================================================
    // INSPECTION & DEBUGGING API
    // =========================================================================

    /**
     * Check if an action hook has any registered callbacks.
     *
     * @param string $hook
     * @return bool
     */
    public function hasAction(string $hook): bool
    {
        return !empty($this->actions[$hook]);
    }

    /**
     * Check if a filter hook has any registered callbacks.
     *
     * @param string $hook
     * @return bool
     */
    public function hasFilter(string $hook): bool
    {
        return !empty($this->filters[$hook]);
    }

    /**
     * Check if a view hook slot has any registered callbacks.
     *
     * @param string $hook
     * @return bool
     */
    public function hasView(string $hook): bool
    {
        return !empty($this->views[$hook]);
    }

    /**
     * Get total count of callbacks registered across all hooks or a specific hook.
     *
     * @param string|null $hook Optional hook name.
     * @param string $type Hook type ('action', 'filter', 'view').
     * @return int
     */
    public function count(?string $hook = null, string $type = 'action'): int
    {
        $repository = match ($type) {
            'filter' => $this->filters,
            'view' => $this->views,
            default => $this->actions,
        };

        if ($hook !== null) {
            if (!isset($repository[$hook])) {
                return 0;
            }
            $total = 0;
            foreach ($repository[$hook] as $callbacks) {
                $total += count($callbacks);
            }
            return $total;
        }

        $total = 0;
        foreach ($repository as $priorities) {
            foreach ($priorities as $callbacks) {
                $total += count($callbacks);
            }
        }
        return $total;
    }

    /**
     * Get active execution priority integers for a specific hook.
     *
     * @param string $hook
     * @param string $type ('action', 'filter', 'view')
     * @return array<int>
     */
    public function priorities(string $hook, string $type = 'action'): array
    {
        $repository = match ($type) {
            'filter' => $this->filters,
            'view' => $this->views,
            default => $this->actions,
        };

        if (!isset($repository[$hook])) {
            return [];
        }

        $keys = array_keys($repository[$hook]);
        sort($keys);
        return $keys;
    }

    /**
     * Remove all registered callbacks across all hooks or for a specific hook.
     *
     * @param string|null $hook Optional specific hook to clear.
     * @return void
     */
    public function removeAll(?string $hook = null): void
    {
        if ($hook !== null) {
            unset($this->actions[$hook], $this->filters[$hook], $this->views[$hook]);
            unset($this->sortedActions[$hook], $this->sortedFilters[$hook], $this->sortedViews[$hook]);
            return;
        }

        $this->actions = [];
        $this->filters = [];
        $this->views = [];
        $this->sortedActions = [];
        $this->sortedFilters = [];
        $this->sortedViews = [];
    }
}