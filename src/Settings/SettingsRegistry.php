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
     * Retrieve the default value for a specific setting key.
     *
     * @param string $key
     * @param mixed $fallback
     * @return mixed
     */
    public function getDefault(string $key, mixed $fallback = null): mixed
    {
        if ($this->defaults === null) {
            $this->buildDefaults();
        }

        return array_key_exists($key, $this->defaults) ? $this->defaults[$key] : $fallback;
    }

    /**
     * Compile all schema defaults into a flat, optimized array.
     *
     * @return void
     */
    protected function buildDefaults(): void
    {
        $this->defaults = [];
        
        // 🚀 Fetch directly from the in-memory Manager instead of config
        $groups = SettingsManager::getGroups();
        
        foreach ($groups as $group) {
            // Retrieve all fields without user authorization filtering
            // so we can resolve defaults globally across the application.
            $fields = $group->getFields(null);
            
            foreach ($fields as $field) {
                $this->defaults[$field->getName()] = $field->default;
            }
        }
    }
}