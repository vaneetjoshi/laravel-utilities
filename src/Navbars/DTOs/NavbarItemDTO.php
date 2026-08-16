<?php

namespace Vaneetjoshi\LaravelUtilities\Navbars\DTOs;

use Illuminate\Support\Facades\Route;

class NavbarItemDTO
{
    public string $id;
    public string $label;
    public ?string $url = null;
    public ?string $route = null;
    public ?string $icon = null;
    public array $roles = [];
    public array $permissions = [];
    public int $order = 0;
    public bool $isDisabled = false;
    public array $activePatterns = [];
    
    /**
     * Custom visibility callback.
     * @var callable|null
     */
    public $visibilityCallback = null;
    
    /**
     * @var array<string, NavbarItemDTO>
     */
    public array $children = [];

    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    public static function make(string $id, string $label): self
    {
        return new self($id, $label);
    }

    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function route(string $route): self
    {
        $this->route = $route;
        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function roles(string|array $roles): self
    {
        $this->roles = (array) $roles;
        return $this;
    }

    public function permissions(string|array $permissions): self
    {
        $this->permissions = (array) $permissions;
        return $this;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function disable(): self
    {
        $this->isDisabled = true;
        return $this;
    }

    public function enable(): self
    {
        $this->isDisabled = false;
        return $this;
    }

    /**
     * Define URL patterns that should trigger an "active" state for this menu item.
     * e.g., ['admin/users', 'admin/users/*']
     */
    public function activePatterns(array $patterns): self
    {
        $this->activePatterns = $patterns;
        return $this;
    }

    /**
     * Define a custom boolean callback to determine visibility.
     */
    public function visibleIf(callable $callback): self
    {
        $this->visibilityCallback = $callback;
        return $this;
    }

    /**
     * Add a nested child to this navigation item. Supports infinite depth.
     */
    public function addChild(NavbarItemDTO $child): self
    {
        $this->children[$child->id] = $child;
        return $this;
    }

    /**
     * Recursively filter and sort children based on their order and disabled status.
     * Validates route existence safely at render time to prevent cross-tenant crashes.
     *
     * @return array<string, NavbarItemDTO>
     */
    public function getSortedChildren(): array
    {
        $activeChildren = array_filter($this->children, function (NavbarItemDTO $child) {
            if ($child->isDisabled) {
                return false;
            }

            // Security/Tenancy Failsafe: Silently drop child items if their route doesn't exist
            if ($child->route !== null && !Route::has($child->route)) {
                return false;
            }

            return true;
        });

        uasort($activeChildren, function (NavbarItemDTO $a, NavbarItemDTO $b) {
            return $a->order <=> $b->order;
        });

        foreach ($activeChildren as $key => $child) {
            $activeChildren[$key]->children = $child->getSortedChildren();
        }

        return $activeChildren;
    }
}