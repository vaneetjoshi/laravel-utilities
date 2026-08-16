<?php

namespace Vaneetjoshi\LaravelUtilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Navbars Facade
 * 
 * @method static \Vaneetjoshi\LaravelUtilities\Navbars\NavbarZone zone(string $name)
 *
 * @see \Vaneetjoshi\LaravelUtilities\Navbars\NavbarManager
 */
class Navbars extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'utilities.navbars';
    }
}