<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Fields;

use Vaneetjoshi\LaravelUtilities\Settings\Enums\InputType;

class SelectField extends Field
{
    public array $options = [];
    public bool $multiple = false;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = InputType::SELECT;
    }

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->type = $multiple ? InputType::MULTI_SELECT : InputType::SELECT;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
            'multiple' => $this->multiple,
        ]);
    }
}