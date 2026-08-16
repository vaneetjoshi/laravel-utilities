<?php

namespace Vaneetjoshi\LaravelUtilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Options Facade
 * 
 * @method static mixed get(string $key, mixed $default = null)
 * @method static mixed set(string $key, mixed $value)
 * @method static void setMany(array $options)
 * @method static bool delete(string $key)
 * @method static bool exists(string $key)
 * @method static void flushCache()
 *
 * @see \Vaneetjoshi\LaravelUtilities\Services\OptionsService
 */
class Options extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'utilities.options';
    }
}