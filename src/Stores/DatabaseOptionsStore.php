<?php

namespace Vaneetjoshi\LaravelUtilities\Stores;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Vaneetjoshi\LaravelUtilities\Contracts\OptionsStoreInterface;

/**
 * Class DatabaseOptionsStore
 * 
 * Primary database storage driver utilizing Laravel Cache to prevent full-table loads.
 * Strictly enforces Tenant-Aware connections, cache prefixing, and Shared Database Isolation.
 */
class DatabaseOptionsStore implements OptionsStoreInterface
{
    /**
     * Resolve the configured database table name.
     *
     * @return string
     */
    protected function getTable(): string
    {
        return config('utilities.options_table', 'options');
    }

    /**
     * 🚀 SMART CONNECTION RESOLVER:
     * Explicitly determine whether to connect to the Central or Tenant database.
     *
     * @return string
     */
    protected function getConnectionName(): string
    {
        if (function_exists('is_tenant_initialized') && is_tenant_initialized()) {
            return config('tenancy.tenant_connection_name', 'mysql_tenant');
        }

        return config('database.default', 'mysql');
    }

    /**
     * Resolve a unique prefix for the active tenant to isolate cache keys.
     *
     * @return string
     */
    protected function getTenantPrefix(): string
    {
        if (function_exists('is_tenant_initialized') && is_tenant_initialized()) {
            if (function_exists('tenant_id') && tenant_id() !== null) {
                return 'tenant_' . tenant_id() . '_';
            }
            
            // Failsafe fallback: use the active database connection name.
            return 'tenant_db_' . DB::connection($this->getConnectionName())->getDatabaseName() . '_';
        }

        return 'global_';
    }

    /**
     * Get the strict, tenant-isolated cache key for an option.
     */
    protected function getCacheKey(string $key): string
    {
        return "utilities_option_" . $this->getTenantPrefix() . $key;
    }

    /**
     * 🚀 SMART QUERY BUILDER: 
     * Applies explicit connection routing and shared database isolation.
     */
    protected function query()
    {
        // Explicitly set the connection before calling the table
        $query = DB::connection($this->getConnectionName())->table($this->getTable());

        if (function_exists('is_tenant_initialized') && is_tenant_initialized() && tenant('database_type') === 'shared') {
            $query->where('website_id', tenant_id());
        }

        return $query;
    }

    /**
     * Purge the runtime memory cache entirely for the current tenant.
     *
     * @return void
     */
    public function flushCache(): void
    {
        $connection = $this->getConnectionName();
        $table = $this->getTable();

        // Ensure the table exists on the explicit connection before attempting to clear
        if (!Schema::connection($connection)->hasTable($table)) {
            return;
        }

        // Fetch only the keys belonging to the current tenant/scope
        $keys = $this->query()->pluck('type');
        foreach ($keys as $key) {
            Cache::forget($this->getCacheKey($key));
        }
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
        $value = Cache::rememberForever($this->getCacheKey($key), function () use ($key) {
            return $this->query()->where('type', $key)->value('value');
        });

        if ($value !== null) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            return $value;
        }

        return $default;
    }

    /**
     * Store or update a key-value option in database storage.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set(string $key, mixed $value): mixed
    {
        $table = $this->getTable();
        $timestamp = now();
        $connection = $this->getConnectionName();
        
        $encodedValue = json_encode($value, JSON_UNESCAPED_UNICODE);

        // Include website_id in the match criteria if shared
        $matchCriteria = ['type' => $key];
        
        if (function_exists('is_tenant_initialized') && is_tenant_initialized() && tenant('database_type') === 'shared') {
            $matchCriteria['website_id'] = tenant_id();
        }

        // 🚀 Use explicit connection for writing
        DB::connection($connection)->table($table)->updateOrInsert(
            $matchCriteria,
            [
                'value' => $encodedValue,
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );

        Cache::forever($this->getCacheKey($key), $encodedValue);

        return $value;
    }

    /**
     * Store multiple key-value options in a single batch operation.
     *
     * @param array<string, mixed> $options
     * @return void
     */
    public function setMany(array $options): void
    {
        foreach ($options as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Delete an option from storage by key.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $deleted = $this->query()->where('type', $key)->delete();
        Cache::forget($this->getCacheKey($key));

        return $deleted > 0;
    }

    /**
     * Check if a specific option key exists in storage.
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return $this->get($key) !== null;
    }
}