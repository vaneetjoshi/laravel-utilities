<?php

namespace Vaneetjoshi\LaravelUtilities\Settings;

/**
 * Class SettingsRegistry
 * 
 * A Request-Lifecycle Singleton that flattens and caches the settings schema.
 * Prevents full schema traversal on every setting() helper call.
 */
class SettingsRegistry
{
    /**
     * In-memory cache of flattened default values.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $defaults = null;

    /**
     * In-memory cache of flattened field types.
     *
     * @var array<string, string>|null
     */
    protected ?array $types = null;

    /**
     * Retrieve the default value for a specific setting key.
     *
     * @param string $key
     * @param mixed $fallback
     * @return mixed
     */
    public function getDefault(string $key, mixed $fallback = null): mixed
    {
        if ($this->defaults === null) {
            $this->buildSchema();
        }

        return array_key_exists($key, $this->defaults) ? $this->defaults[$key] : $fallback;
    }

    /**
     * Retrieve the field type for a specific setting key.
     *
     * @param string $key
     * @return string|null
     */
    public function getFieldType(string $key): ?string
    {
        if ($this->types === null) {
            $this->buildSchema();
        }

        return array_key_exists($key, $this->types) ? $this->types[$key] : null;
    }

    /**
     * Compile all schema defaults and types into flat, optimized arrays.
     *
     * @return void
     */
    protected function buildSchema(): void
    {
        $this->defaults = [];
        $this->types = [];
        
        // Fetch directly from the in-memory Manager instead of config
        $groups = SettingsManager::getGroups();
        
        foreach ($groups as $group) {
            // Retrieve all fields without user authorization filtering
            // so we can resolve defaults globally across the application.
            $fields = $group->getFields(null);
            
            foreach ($fields as $field) {
                $this->defaults[$field->getName()] = $field->default;
                $this->types[$field->getName()] = $field->type->value;
            }
        }
    }
}