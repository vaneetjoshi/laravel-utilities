<?php

namespace Vaneetjoshi\LaravelUtilities\Navbars;

use Illuminate\Support\Facades\Route;
use Vaneetjoshi\LaravelUtilities\Services\HookService;
use Vaneetjoshi\LaravelUtilities\Navbars\DTOs\NavbarItemDTO;

/**
 * Class NavbarZone
 * 
 * Manages the registration, retrieval, sorting, and authorization of navigation items
 * for a specific UI zone (e.g., 'tenant_admin_navbar').
 */
class NavbarZone
{
    /**
     * The unique identifier for this navbar zone.
     * 
     * @var string
     */
    protected string $zoneName;

    /**
     * The hook service used to dispatch and apply filters.
     * 
     * @var HookService
     */
    protected HookService $hooks;

    /**
     * NavbarZone constructor.
     *
     * @param string $zoneName The unique identifier for the zone.
     * @param HookService $hooks The HookService singleton.
     */
    public function __construct(string $zoneName, HookService $hooks)
    {
        $this->zoneName = $zoneName;
        $this->hooks = $hooks;
    }

    /**
     * Register a callback to mutate or add items to this navbar zone.
     * Maps directly to HookService::addFilter().
     * Automatically converts numeric array indices to associative IDs for easier extensibility.
     *
     * @param callable|array $itemOrCallback Function accepting (array $items, $user) or raw array.
     * @param int $priority Execution priority (lower executes first).
     * 
     * @return void
     */
    public function add(callable|array $itemOrCallback, int $priority = 10): void
    {
        $hookName = "navbar_zone_{$this->zoneName}";

        if (is_array($itemOrCallback) && !is_callable($itemOrCallback)) {
            
            $wrapper = function (array $items) use ($itemOrCallback) {
                $id = $itemOrCallback['id'] ?? uniqid('nav_');
                $items[$id] = $itemOrCallback;
                return $items;
            };

        } else {
            
            $wrapper = function (array $items, ...$args) use ($itemOrCallback) {
                $modifiedItems = call_user_func($itemOrCallback, $items, ...$args);
                
                $finalItems = [];
                foreach ($modifiedItems as $currentKey => $item) {
                    // FIX: Check if it is a NavbarItemDTO object, NOT an array!
                    if ($item instanceof \Vaneetjoshi\LaravelUtilities\Navbars\DTOs\NavbarItemDTO) {
                        // Set the array key to the DTO's id
                        $finalItems[$item->id] = $item;
                    } else {
                        $finalItems[$currentKey] = $item;
                    }
                }
                return $finalItems;
            };

        }

        $this->hooks->addFilter($hookName, $wrapper, $priority);
    }

    /**
     * Retrieve all active, sorted root items for this navbar zone.
     * Automatically merges duplicate IDs, recursively sorts infinite-depth children, 
     * enforces authorization, and performs context-aware route validation.
     *
     * @return array<string, NavbarItemDTO>
     * 
     * @throws \InvalidArgumentException If a defined route does not exist.
     */
    public function get(): array
    {
        $hookName = "navbar_zone_{$this->zoneName}";
        
        $user = $this->resolveUser();

        // 1. Retrieve raw items from all registered hooks
        $rawItems = $this->hooks->applyFilters($hookName, [], $user);

        // 2. Merge Duplicate Groups (Module Extensibility)
        $mergedItems = [];
        foreach ($rawItems as $item) {
            if (!$item instanceof NavbarItemDTO) {
                continue;
            }

            if (isset($mergedItems[$item->id])) {
                // If the group already exists, merge the new children into the existing parent group
                foreach ($item->children as $child) {
                    $mergedItems[$item->id]->addChild($child);
                }
            } else {
                // Otherwise, register it as a new group
                $mergedItems[$item->id] = $item;
            }
        }

        // 3. Filter for Authorization, Visibility, and Validation
        $activeRootItems = array_filter($mergedItems, function ($item) use ($user) {
            if ($item->isDisabled) {
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

        // 4. Sort Root Items
        uasort($activeRootItems, function (NavbarItemDTO $a, NavbarItemDTO $b) {
            return $a->order <=> $b->order;
        });

        // 5. Trigger recursive sorting and validation for all child nodes
        foreach ($activeRootItems as $key => $item) {
            $activeRootItems[$key]->children = $item->getSortedChildren();
        }

        return $activeRootItems;
    }

    /**
     * Resolve the authenticated user, automatically adapting to the Tenancy Engine if active.
     * 
     * @return mixed The authenticated User model, or null if unauthenticated.
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