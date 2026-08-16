<?php

namespace Vaneetjoshi\LaravelUtilities\Widgets;

use Vaneetjoshi\LaravelUtilities\Services\HookService;

class WidgetManager
{
    protected HookService $hooks;

    public function __construct(HookService $hooks)
    {
        $this->hooks = $hooks;
    }

    /**
     * Retrieve a WidgetZone builder instance.
     */
    public function zone(string $name): WidgetZone
    {
        return new WidgetZone($name, $this->hooks);
    }
}