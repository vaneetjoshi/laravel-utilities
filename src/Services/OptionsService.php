<?php

namespace Vaneetjoshi\LaravelUtilities\Services;

use Vaneetjoshi\LaravelUtilities\Contracts\OptionsStoreInterface;

/**
 * Class OptionsService
 * 
 * Why we need this file:
 * Wraps OptionsStoreInterface to provide clean dependency injection and method chaining.
 */
class OptionsService
{
    /**
     * Underlying storage driver.
     *
     * @var OptionsStoreInterface
     */
    protected OptionsStoreInterface $store;

    /**
     * OptionsService constructor.
     *
     * @param OptionsStoreInterface $store
     */
    public function __construct(OptionsStoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * Retrieve an option value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store->get($key, $default);
    }

    /**
     * Store or update a key-value option.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set(string $key, mixed $value): mixed
    {
        return $this->store->set($key, $value);
    }

    /**
     * Store multiple key-value options in a single batch operation.
     *
     * @param array<string, mixed> $options
     * @return void
     */
    public function setMany(array $options): void
    {
        $this->store->setMany($options);
    }

    /**
     * Delete an option by key.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->store->delete($key);
    }

    /**
     * Check if an option exists.
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return $this->store->exists($key);
    }

    /**
     * Flush memory cache.
     *
     * @return void
     */
    public function flushCache(): void
    {
        $this->store->flushCache();
    }

    /**
     * Get underlying store instance.
     *
     * @return OptionsStoreInterface
     */
    public function getStore(): OptionsStoreInterface
    {
        return $this->store;
    }
}