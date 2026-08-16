<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Fields;

use Vaneetjoshi\LaravelUtilities\Settings\Enums\InputType;

class NumberField extends Field
{
    public ?float $min = null;
    public ?float $max = null;
    public ?float $step = null;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = InputType::NUMBER;
    }

    public function min(float $min): static { $this->min = $min; return $this; }
    public function max(float $max): static { $this->max = $max; return $this; }
    public function step(float $step): static { $this->step = $step; return $this; }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
        ]);
    }
}