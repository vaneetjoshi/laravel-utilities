<?php

namespace Vaneetjoshi\LaravelUtilities\Widgets;

use Vaneetjoshi\LaravelUtilities\Services\HookService;
use Vaneetjoshi\LaravelUtilities\Widgets\DTOs\WidgetDTO;

class WidgetZone
{
    protected string $zoneName;
    protected HookService $hooks;

    public function __construct(string $zoneName, HookService $hooks)
    {
        $this->zoneName = $zoneName;
        $this->hooks = $hooks;
    }

    /**
     * Register a callback to mutate or add widgets to this zone.
     * Maps directly to HookService::addFilter().
     *
     * @param callable|string|array $callback Function accepting (array $widgets, $user)
     * @param int $priority Execution priority (lower executes first)
     */
    public function add(callable|string|array $callback, int $priority = 10): void
    {
        $hookName = "widget_zone_{$this->zoneName}";
        $this->hooks->addFilter($hookName, $callback, $priority);
    }

    /**
     * Retrieve all active, sorted widgets for this zone.
     * Automatically resolves the current user and injects it into the filter payload.
     *
     * @return array<string, WidgetDTO>
     */
    public function get(): array
    {
        $hookName = "widget_zone_{$this->zoneName}";
        
        $user = $this->resolveUser();

        $widgets = $this->hooks->applyFilters($hookName, [], $user);

        $activeWidgets = array_filter($widgets, function ($widget) use ($user) {
            if (!$widget instanceof WidgetDTO || $widget->isDisabled) {
                return false;
            }

            if ($widget->visibilityCallback !== null && is_callable($widget->visibilityCallback)) {
                if (!call_user_func($widget->visibilityCallback, $user)) {
                    return false;
                }
            }

            return true;
        });

        uasort($activeWidgets, function (WidgetDTO $a, WidgetDTO $b) {
            return $a->order <=> $b->order;
        });

        return $activeWidgets;
    }

    /**
     * Resolve the authenticated user, automatically adapting to the Tenancy Engine if active.
     */
    protected function resolveUser(): mixed
    {
        if (function_exists('tenant_auth') && function_exists('is_tenant_initialized') && is_tenant_initialized()) {
            return tenant_auth()->user();
        }

        if (function_exists('auth')) {
            return auth()->user();
        }

        return null;
    }
}