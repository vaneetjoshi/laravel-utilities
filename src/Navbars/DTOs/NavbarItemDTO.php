<?php

namespace Vaneetjoshi\LaravelUtilities\Navbars\DTOs;

use Illuminate\Support\Facades\Route;

/**
 * Class NavbarItemDTO
 * 
 * Data Transfer Object for representing navigation items in the system.
 */
class NavbarItemDTO
{
    /** @var string Unique identifier for the navbar item. */
    public string $id;

    /** @var string Display label for the navbar item. */
    public string $label;

    /** @var string|null Absolute or relative URL for the item. */
    public ?string $url = null;

    /** @var string|null Named route for the item. */
    public ?string $route = null;

    /** @var string|null Icon identifier (e.g., Iconify string or view name). */
    public ?string $icon = null;

    /** @var array<string> Roles required to view this item. */
    public array $roles = [];

    /** @var array<string> Permissions required to view this item. */
    public array $permissions = [];

    /** @var int Sorting order (lower numbers appear first). */
    public int $order = 0;

    /** @var bool Flag indicating if the item is disabled. */
    public bool $isDisabled = false;

    /** @var array<string> URL patterns that mark this item as active. */
    public array $activePatterns = [];
    
    /**
     * Custom visibility callback.
     * 
     * @var callable|null
     */
    public $visibilityCallback = null;
    
    /**
     * @var array<string, NavbarItemDTO> Nested child items.
     */
    public array $children = [];

    /**
     * NavbarItemDTO constructor.
     *
     * @param string $id Unique identifier.
     * @param string $label Display label.
     */
    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    /**
     * Static factory method to create a new instance.
     *
     * @param string $id Unique identifier.
     * @param string $label Display label.
     * @return self
     */
    public static function make(string $id, string $label): self
    {
        return new self($id, $label);
    }

    /**
     * Set the URL.
     *
     * @param string $url The explicit URL.
     * @return self
     */
    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set the named route.
     *
     * @param string $route The Laravel route name.
     * @return self
     */
    public function route(string $route): self
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Set the icon.
     *
     * @param string $icon The icon identifier.
     * @return self
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set the required roles.
     *
     * @param string|array<string> $roles The roles authorized to see this item.
     * @return self
     */
    public function roles(string|array $roles): self
    {
        $this->roles = (array) $roles;
        return $this;
    }

    /**
     * Set the required permissions.
     *
     * @param string|array<string> $permissions The permissions required to see this item.
     * @return self
     */
    public function permissions(string|array $permissions): self
    {
        $this->permissions = (array) $permissions;
        return $this;
    }

    /**
     * Set the display order.
     *
     * @param int $order The sorting order.
     * @return self
     */
    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Disable the item.
     *
     * @return self
     */
    public function disable(): self
    {
        $this->isDisabled = true;
        return $this;
    }

    /**
     * Enable the item.
     *
     * @return self
     */
    public function enable(): self
    {
        $this->isDisabled = false;
        return $this;
    }

    /**
     * Define URL patterns that should trigger an "active" state for this menu item.
     *
     * @param array<string> $patterns The patterns to match.
     * @return self
     */
    public function activePatterns(array $patterns): self
    {
        $this->activePatterns = $patterns;
        return $this;
    }

    /**
     * Define a custom boolean callback to determine visibility.
     *
     * @param callable $callback The visibility condition callback.
     * @return self
     */
    public function visibleIf(callable $callback): self
    {
        $this->visibilityCallback = $callback;
        return $this;
    }

    /**
     * Add a nested child to this navigation item. Supports infinite depth.
     * Automatically preserves the parent's link as a child if it transitions to a folder.
     *
     * @param NavbarItemDTO $child The child item to add.
     * @return self
     */
    public function addChild(NavbarItemDTO $child): self
    {
        // SMART FOLDER TRANSITION:
        // If this is the FIRST child being added, and the parent has a direct link,
        // we must preserve the parent's link by converting it into an "Index" child.
        if (empty($this->children) && ($this->route !== null || $this->url !== null)) {
            
            $indexChild = self::make("{$this->id}_index", $this->label)
                ->roles($this->roles)
                ->permissions($this->permissions)
                ->activePatterns($this->activePatterns)
                ->setOrder(-1); // Ensure the original link stays at the very top of the new dropdown

            if ($this->route !== null) {
                $indexChild->route($this->route);
                $this->route = null; // Clear parent route to make it a pure folder toggle
            }

            if ($this->url !== null) {
                $indexChild->url($this->url);
                $this->url = null; // Clear parent URL to make it a pure folder toggle
            }

            // Register the generated index child
            $this->children[$indexChild->id] = $indexChild;
        }

        // Register the new child that was passed to the method
        $this->children[$child->id] = $child;
        
        return $this;
    }

    /**
     * Recursively filter and sort children based on their order and disabled status.
     * Validates route existence safely at render time and throws exceptions for missing routes.
     *
     * @return array<string, NavbarItemDTO>
     * @throws \InvalidArgumentException If a defined route does not exist.
     */
    public function getSortedChildren(): array
    {
        $activeChildren = array_filter($this->children, function (NavbarItemDTO $child) {
            if ($child->isDisabled) {
                return false;
            }

            // Strictly throw an exception if the route is invalid, matching root item behavior
            if ($child->route !== null && !Route::has($child->route)) {
                throw new \InvalidArgumentException("Navbar child item '{$child->label}' references an invalid route: '{$child->route}'.");
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