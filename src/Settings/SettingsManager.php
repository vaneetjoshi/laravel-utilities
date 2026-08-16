<?php

namespace Vaneetjoshi\LaravelUtilities\Settings;

use Vaneetjoshi\LaravelUtilities\Settings\Schema\Group;
use Vaneetjoshi\LaravelUtilities\Settings\Fields\Field;
use Vaneetjoshi\LaravelUtilities\Settings\Fields\SelectField;
use Vaneetjoshi\LaravelUtilities\Settings\Fields\NumberField;
use Vaneetjoshi\LaravelUtilities\Settings\Fields\FileField;
use Vaneetjoshi\LaravelUtilities\Settings\Fields\ArrayField;
use Vaneetjoshi\LaravelUtilities\Settings\Enums\InputType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingsManager
{
    /**
     * Store all imperatively registered groups in memory.
     */
    public static array $registeredGroups = [];

    /**
     * Create and automatically register a new Settings Group.
     */
    public static function group(string $name): Group 
    { 
        $group = new Group($name);
        self::$registeredGroups[$name] = $group; // Automatically store in memory
        return $group;
    }

    public static function getGroups(): array
    {
        return self::$registeredGroups;
    }

    public static function getGroup(string $name): ?Group
    {
        return self::$registeredGroups[$name] ?? null;
    }

    public static function text(string $name): Field { return (new Field($name))->type(InputType::TEXT); }
    public static function email(string $name): Field { return (new Field($name))->type(InputType::EMAIL); }
    public static function password(string $name): Field { return (new Field($name))->type(InputType::PASSWORD); }
    public static function textarea(string $name): Field { return (new Field($name))->type(InputType::TEXTAREA); }
    public static function checkbox(string $name): Field { return (new Field($name))->type(InputType::CHECKBOX); }
    public static function date(string $name): Field { return (new Field($name))->type(InputType::DATE); }
    public static function datetime(string $name): Field { return (new Field($name))->type(InputType::DATETIME); }
    public static function keyValue(string $name): Field { return (new Field($name))->type(InputType::KEY_VALUE); }
    public static function select(string $name): SelectField { return new SelectField($name); }
    public static function number(string $name): NumberField { return new NumberField($name); }
    public static function file(string $name): FileField { return new FileField($name); }
    public static function image(string $name): FileField { return (new FileField($name))->type(InputType::IMAGE); }
    public static function array(string $name): ArrayField { return new ArrayField($name); }

    public static function save(Group $group, array $validatedData, mixed $user = null): void
    {
        $bulkOptions = [];

        foreach ($group->getFields($user) as $field) {
            $fieldName = $field->getName();

            // Check if the field is present in the validated data payload
            if (! array_key_exists($fieldName, $validatedData)) {
                // FIX: If it's an ArrayField and it's missing, the user deleted all rows.
                // We must explicitly set it to an empty array so it overwrites the old DB value.
                if ($field instanceof ArrayField) {
                    $value = [];
                } else {
                    continue;
                }
            } else {
                $value = $validatedData[$fieldName];
            }

            // Re-index arrays recursively to strip out JS timestamps on all levels
            if ($field instanceof ArrayField && is_array($value)) {
                $value = self::recursivelyReindexArray($value, $field->getSchema());
            }

            if ($field instanceof FileField && $value instanceof UploadedFile) {
                
                // SECURITY PATCH: Isolate physical file uploads to prevent cross-tenant overwrite
                $tenantPrefix = function_exists('is_tenant_initialized') && is_tenant_initialized() && function_exists('tenant_id') && tenant_id() !== null 
                    ? '/tenant_' . tenant_id() 
                    : '/global';
                    
                $secureDirectory = rtrim($field->directory, '/') . $tenantPrefix;

                $oldFilePath = getOption($fieldName, null);
                if ($oldFilePath && Storage::disk($field->disk)->exists($oldFilePath)) {
                    Storage::disk($field->disk)->delete($oldFilePath);
                }
                
                $value = $value->store($secureDirectory, $field->disk);
                setOption($fieldName, $value);
                continue;
            }

            $bulkOptions[$fieldName] = $value;
        }

        if (! empty($bulkOptions)) {
            setManyOptions($bulkOptions);
        }
    }

    /**
     * Recursively resets numeric keys to fix order after drag-and-drop actions on nested arrays.
     */
    private static function recursivelyReindexArray(array $data, array $schema): array
    {
        $reindexed = array_values($data);
        
        $nestedArrayFields = array_filter($schema, fn($f) => $f instanceof ArrayField);
        
        if (empty($nestedArrayFields)) {
            return $reindexed;
        }

        foreach ($reindexed as $index => &$row) {
            if (!is_array($row)) continue;
            
            foreach ($nestedArrayFields as $nestedField) {
                $nestedName = $nestedField->getName();
                if (isset($row[$nestedName]) && is_array($row[$nestedName])) {
                    $row[$nestedName] = self::recursivelyReindexArray($row[$nestedName], $nestedField->getSchema());
                }
            }
        }

        return $reindexed;
    }
}