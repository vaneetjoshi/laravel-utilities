<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Fields;

use Vaneetjoshi\LaravelUtilities\Settings\Enums\InputType;
use Illuminate\Support\Arr;

class ArrayField extends Field
{
    protected array $schema = [];
    public ?int $minRows = null;
    public ?int $maxRows = null;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = InputType::ARRAY;
    }

    public function schema(array $fields): static
    {
        $this->schema = $fields;
        return $this;
    }

    public function minRows(int $min): static
    {
        $this->minRows = $min;
        return $this;
    }

    public function maxRows(int $max): static
    {
        $this->maxRows = $max;
        return $this;
    }

    public function getRules(string $prefix = ''): array
    {
        $key = $prefix ? "{$prefix}.{$this->name}" : $this->name;
        
        $baseRules = ['array'];
        if ($this->minRows !== null) {
            $baseRules[] = "min:{$this->minRows}";
        }
        if ($this->maxRows !== null) {
            $baseRules[] = "max:{$this->maxRows}";
        }
        
        $rules = [$key => array_merge($baseRules, $this->rules)];

        foreach ($this->schema as $field) {
            $nestedRules = $field->getRules("{$key}.*"); 
            $rules = array_merge($rules, $nestedRules);
        }

        return $rules;
    }
    
    public function getValidationAttributes(array $submittedData, string $prefix = ''): array
    {
        $key = $prefix ? "{$prefix}.{$this->name}" : $this->name;
        $attributes = [$key => $this->label];

        $submittedArray = Arr::get($submittedData, $key);

        if (is_array($submittedArray)) {
            $rowIndex = 1;
            foreach ($submittedArray as $rowKey => $rowData) {
                foreach ($this->schema as $subField) {
                    $subPrefix = "{$key}.{$rowKey}";
                    $subAttributes = $subField->getValidationAttributes($submittedData, $subPrefix);
                    
                    foreach ($subAttributes as $attrKey => $attrLabel) {
                         if ($attrKey === "{$subPrefix}." . $subField->getName()) {
                             $attributes[$attrKey] = $this->label . ' Row ' . $rowIndex . ' ' . $subField->label;
                         } else {
                             $attributes[$attrKey] = $this->label . ' Row ' . $rowIndex . ' ' . $attrLabel;
                         }
                    }
                }
                $rowIndex++;
            }
        }

        return $attributes;
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'schema' => array_map(fn($field) => $field->toArray(), $this->schema),
            'minRows' => $this->minRows,
            'maxRows' => $this->maxRows,
        ]);
    }
}