<?php

namespace Vaneetjoshi\LaravelUtilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Widgets Facade
 * 
 * @method static \Vaneetjoshi\LaravelUtilities\Widgets\WidgetZone zone(string $name)
 *
 * @see \Vaneetjoshi\LaravelUtilities\Widgets\WidgetManager
 */
class Widgets extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'utilities.widgets';
    }
}