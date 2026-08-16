<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Fields;

use Vaneetjoshi\LaravelUtilities\Settings\Contracts\FieldContract;
use Vaneetjoshi\LaravelUtilities\Settings\Enums\InputType;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class Field implements FieldContract
{
    public string $name;
    public InputType $type = InputType::TEXT;
    public string $label;
    public ?string $placeholder = null;
    public ?string $helpText = null;
    public array $rules = [];
    public mixed $default = null;
    public ?string $dependsOnField = null;
    public mixed $dependsOnValue = null;
    protected ?string $customView = null;
    
    protected array $roles = [];
    protected array $permissions = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = Str::headline($name);
    }

    public function type(InputType $type): static { $this->type = $type; return $this; }
    public function label(string $label): static { $this->label = $label; return $this; }
    public function placeholder(string $placeholder): static { $this->placeholder = $placeholder; return $this; }
    public function helpText(string $helpText): static { $this->helpText = $helpText; return $this; }
    public function rules(array|string $rules): static { $this->rules = is_string($rules) ? explode('|', $rules) : $rules; return $this; }
    public function default(mixed $default): static { $this->default = $default; return $this; }
    public function view(string $viewPath): static { $this->customView = $viewPath; return $this; }

    public function dependsOn(string $field, mixed $value = '1'): static
    {
        $this->dependsOnField = $field;
        if (is_array($value)) {
            $this->dependsOnValue = $value;
        } else {
            $this->dependsOnValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        }
        return $this;
    }

    public function getDependsOnField(): ?string { return $this->dependsOnField; }
    public function getDependsOnValue(): mixed { return $this->dependsOnValue; }
    public function getName(): string { return $this->name; }
    public function roles(string|array $roles): static { $this->roles = (array) $roles; return $this; }
    public function permissions(string|array $permissions): static { $this->permissions = (array) $permissions; return $this; }

    public function isAuthorized(mixed $user): bool
    {
        if (empty($this->roles) && empty($this->permissions)) return true;
        if (! $user) return false;
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) return true;
        if (! empty($this->permissions) && method_exists($user, 'hasPermission') && $user->hasPermission($this->permissions)) return true;
        if (! empty($this->roles) && method_exists($user, 'hasRole') && $user->hasRole($this->roles)) return true;
        return false;
    }

    public function getRules(string $prefix = ''): array
    {
        $key = $prefix ? "{$prefix}.{$this->name}" : $this->name;
        $finalRules = $this->rules;

        // Security Fix: Prevent silent dropping of inputs by Laravel's validator if no rules are specified.
        if (empty($finalRules)) {
            $finalRules = ['nullable'];
        }

        if ($this->dependsOnField !== null) {
            $finalRules = array_filter($finalRules, fn($r) => $r !== 'required');
            $finalRules[] = Rule::requiredIf(function () {
                $submittedValue = request()->input($this->dependsOnField);
                if ($submittedValue === null) return false;
                $req = $this->dependsOnValue;
                $isReqArr = is_array($req);
                $isSubArr = is_array($submittedValue);
                if ($isReqArr && $isSubArr) {
                    foreach ($req as $r) if (in_array((string)$r, $submittedValue)) return true;
                    return false;
                } elseif ($isReqArr && !$isSubArr) return in_array((string)$submittedValue, array_map('strval', $req));
                elseif (!$isReqArr && $isSubArr) return in_array((string)$req, $submittedValue);
                return (string)$submittedValue === (string)$req;
            });
            if (!in_array('nullable', $finalRules)) array_unshift($finalRules, 'nullable');
            $finalRules = array_values($finalRules);
        }
        return [$key => $finalRules];
    }

    public function getValidationAttributes(array $submittedData, string $prefix = ''): array
    {
        $key = $prefix ? "{$prefix}.{$this->name}" : $this->name;
        return [$key => $this->label];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'helpText' => $this->helpText,
            'default' => $this->default,
            'dependsOnField' => $this->dependsOnField,
            'dependsOnValue' => $this->dependsOnValue,
        ];
    }

    public function render(mixed $currentValue = null, ?string $nameOverride = null): View|string
    {
        $view = $this->customView ?? 'utilities::settings.fields.' . $this->type->value;
        $resolvedName = $nameOverride ?? $this->name;
        
        $dotNotationName = str_replace(['[', ']'], ['.', ''], $resolvedName);
        $dotNotationName = str_replace('..', '.', $dotNotationName);
        $dotNotationName = rtrim($dotNotationName, '.');
        
        $dbValue = function_exists('getOption') ? getOption($this->name, $this->default) : $this->default;
        $finalValue = $currentValue ?? old($dotNotationName, $dbValue);
        
        return view($view, [
            'field' => $this,
            'value' => $finalValue,
            'name'  => $resolvedName,
        ]);
    }
}