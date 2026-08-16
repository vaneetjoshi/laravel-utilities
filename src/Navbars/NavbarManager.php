<?php

namespace Vaneetjoshi\LaravelUtilities\Navbars;

use Vaneetjoshi\LaravelUtilities\Services\HookService;

class NavbarManager
{
    protected HookService $hooks;

    public function __construct(HookService $hooks)
    {
        $this->hooks = $hooks;
    }

    /**
     * Retrieve a NavbarZone builder instance.
     */
    public function zone(string $name): NavbarZone
    {
        return new NavbarZone($name, $this->hooks);
    }
}