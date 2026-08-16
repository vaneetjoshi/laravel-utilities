<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Schema;

use Vaneetjoshi\LaravelUtilities\Settings\Contracts\GroupContract;
use Illuminate\Support\Str;

class Group implements GroupContract
{
    public string $name;
    public string $label;
    public ?string $description = null;
    public ?string $icon = null;
    protected array $fields = [];
    
    protected array $roles = [];
    protected array $permissions = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = Str::headline($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }
    
    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon(): string
    {
        if ($this->icon) {
            return $this->icon;
        }
        return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>';
    }

    public function roles(string|array $roles): static
    {
        $this->roles = (array) $roles;
        return $this;
    }

    public function permissions(string|array $permissions): static
    {
        $this->permissions = (array) $permissions;
        return $this;
    }

    public function isAuthorized(mixed $user): bool
    {
        if (empty($this->roles) && empty($this->permissions)) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (! empty($this->permissions) && method_exists($user, 'hasPermission')) {
            if ($user->hasPermission($this->permissions)) {
                return true;
            }
        }

        if (! empty($this->roles) && method_exists($user, 'hasRole')) {
            if ($user->hasRole($this->roles)) {
                return true;
            }
        }

        return false;
    }

    public function addFields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }

    public function getName(): string { return $this->name; }
    
    public function getFields(mixed $user = null): array 
    { 
        if (!$user) {
            return $this->fields;
        }
        
        return array_filter($this->fields, fn($field) => $field->isAuthorized($user)); 
    }

    public function getValidationRules(mixed $user = null): array
    {
        $rules = [];
        foreach ($this->getFields($user) as $field) {
            $rules = array_merge($rules, $field->getRules());
        }
        return $rules;
    }
    
    public function getValidationAttributes(array $submittedData, mixed $user = null): array
    {
        $attributes = [];
        foreach ($this->getFields($user) as $field) {
            $attributes = array_merge($attributes, $field->getValidationAttributes($submittedData));
        }
        return $attributes;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'description' => $this->description,
            'icon' => $this->getIcon(),
            'fields' => array_map(fn($field) => $field->toArray(), $this->fields),
        ];
    }
}