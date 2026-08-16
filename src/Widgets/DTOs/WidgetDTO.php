<?php

namespace Vaneetjoshi\LaravelUtilities\Widgets\DTOs;

use Vaneetjoshi\LaravelUtilities\Widgets\Enums\WidgetColor;
use Vaneetjoshi\LaravelUtilities\Widgets\Enums\WidgetIcon;

class WidgetDTO
{
    public string $id;
    public ?string $title = null;
    public mixed $value = null;
    public ?string $icon = null;
    public ?string $color = null;
    public ?string $view = null;
    public int $order = 0;
    public bool $isDisabled = false;
    
    /**
     * Custom visibility callback.
     * @var callable|null
     */
    public $visibilityCallback = null;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function make(string $id): self
    {
        return new self($id);
    }

    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function value(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function icon(WidgetIcon|string $icon): self
    {
        $this->icon = $icon instanceof WidgetIcon ? $icon->value : $icon;
        return $this;
    }

    public function color(WidgetColor|string $color): self
    {
        $this->color = $color instanceof WidgetColor ? $color->value : $color;
        return $this;
    }

    public function view(string $viewPath): self
    {
        $this->view = $viewPath;
        return $this;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function disable(): self
    {
        $this->isDisabled = true;
        return $this;
    }

    public function enable(): self
    {
        $this->isDisabled = false;
        return $this;
    }

    public function visibleIf(callable $callback): self
    {
        $this->visibilityCallback = $callback;
        return $this;
    }
}