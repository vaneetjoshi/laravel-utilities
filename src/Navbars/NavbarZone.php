<?php

namespace Vaneetjoshi\LaravelUtilities\Navbars;

use Illuminate\Support\Facades\Route;
use Vaneetjoshi\LaravelUtilities\Services\HookService;
use Vaneetjoshi\LaravelUtilities\Navbars\DTOs\NavbarItemDTO;

class NavbarZone
{
    protected string $zoneName;
    protected HookService $hooks;

    public function __construct(string $zoneName, HookService $hooks)
    {
        $this->zoneName = $zoneName;
        $this->hooks = $hooks;
    }

    /**
     * Register a callback to mutate or add items to this navbar zone.
     * Maps directly to HookService::addFilter().
     *
     * @param callable|string|array $callback Function accepting (array $items, $user)
     * @param int $priority Execution priority (lower executes first)
     */
    public function add(callable|string|array $callback, int $priority = 10): void
    {
        $hookName = "navbar_zone_{$this->zoneName}";
        $this->hooks->addFilter($hookName, $callback, $priority);
    }

    /**
     * Retrieve all active, sorted root items for this navbar zone.
     * Recursively sorts infinite-depth children, enforces authorization, 
     * and performs context-aware route validation at render time.
     *
     * @return array<string, NavbarItemDTO>
     */
    public function get(): array
    {
        $hookName = "navbar_zone_{$this->zoneName}";
        
        $user = $this->resolveUser();

        $items = $this->hooks->applyFilters($hookName, [], $user);

        $activeRootItems = array_filter($items, function ($item) use ($user) {
            if (!$item instanceof NavbarItemDTO || $item->isDisabled) {
                return false;
            }
            
            // Throw an exception instead of silently dropping the root item
            if ($item->route !== null && !Route::has($item->route)) {
                throw new \InvalidArgumentException("Navbar root item '{$item->label}' references an invalid route: '{$item->route}'.");
            }

            // Enforce explicit boolean visibility callback
            if ($item->visibilityCallback !== null && is_callable($item->visibilityCallback)) {
                if (!call_user_func($item->visibilityCallback, $user)) {
                    return false;
                }
            }

            // Enforce Role Checks
            if (!empty($item->roles)) {
                if (!$user || !method_exists($user, 'hasRole') || !$user->hasRole($item->roles)) {
                    return false;
                }
            }

            // Enforce Permission Checks
            if (!empty($item->permissions)) {
                if (!$user || !method_exists($user, 'hasPermission') || !$user->hasPermission($item->permissions)) {
                    return false;
                }
            }

            return true;
        });

        uasort($activeRootItems, function (NavbarItemDTO $a, NavbarItemDTO $b) {
            return $a->order <=> $b->order;
        });

        // Trigger recursive sorting and validation for all child nodes
        foreach ($activeRootItems as $key => $item) {
            $activeRootItems[$key]->children = $item->getSortedChildren();
        }

        return $activeRootItems;
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