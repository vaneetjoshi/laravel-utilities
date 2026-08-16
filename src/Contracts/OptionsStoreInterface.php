<?php

namespace Vaneetjoshi\LaravelUtilities\Contracts;

/**
 * Interface OptionsStoreInterface
 * 
 * Why we need this file:
 * Establishes a standard contract for key-value persistence using intuitive names
 * that match developer expectations: get(), set(), setMany(), delete(), exists().
 */
interface OptionsStoreInterface
{
    /**
     * Retrieve an option value by its unique key identifier.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store or update a key-value option in storage.
     *
     * @param string $key The unique option identifier.
     * @param mixed $value The value to persist.
     * @return mixed The saved value.
     */
    public function set(string $key, mixed $value): mixed;

    /**
     * Store multiple key-value options in a single batch operation.
     *
     * @param array<string, mixed> $options Associative array of key-value pairs.
     * @return void
     */
    public function setMany(array $options): void;

    /**
     * Delete an option from storage by its unique key identifier.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Check if a specific option key exists in storage.
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * Purge all runtime memory caches associated with the storage driver.
     *
     * @return void
     */
    public function flushCache(): void;
}