<?php

namespace Vaneetjoshi\LaravelUtilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Hooks Facade
 * 
 * @method static void addAction(string $hook, callable|string|array $callback, int $priority = 10)
 * @method static bool removeAction(string $hook, callable|string|array $callback, ?int $priority = null)
 * @method static void doAction(string $hook, mixed ...$args)
 * @method static void addFilter(string $hook, callable|string|array $callback, int $priority = 10)
 * @method static bool removeFilter(string $hook, callable|string|array $callback, ?int $priority = null)
 * @method static mixed applyFilters(string $hook, mixed $value, mixed ...$args)
 * @method static void addView(string $hook, callable|string|array $callback, int $priority = 10)
 * @method static bool removeView(string $hook, callable|string|array $callback, ?int $priority = null)
 * @method static string render(string $hook, mixed ...$args)
 * @method static string renderViewHook(string $hook, mixed ...$args)
 * @method static bool hasAction(string $hook)
 * @method static bool hasFilter(string $hook)
 * @method static bool hasView(string $hook)
 * @method static int count(?string $hook = null, string $type = 'action')
 * @method static array priorities(string $hook, string $type = 'action')
 * @method static void removeAll(?string $hook = null)
 *
 * @see \Vaneetjoshi\LaravelUtilities\Services\HookService
 */
class Hooks extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'utilities.hooks';
    }
}