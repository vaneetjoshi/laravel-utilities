<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Fields;

use Vaneetjoshi\LaravelUtilities\Settings\Enums\InputType;

class FileField extends Field
{
    public string $disk = 'public';
    public string $directory = 'settings';
    public array $acceptedFileTypes = [];

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type = InputType::FILE;
    }

    public function disk(string $disk): static { $this->disk = $disk; return $this; }
    public function directory(string $directory): static { $this->directory = $directory; return $this; }
    public function accept(array $mimes): static { $this->acceptedFileTypes = $mimes; return $this; }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'disk' => $this->disk,
            'directory' => $this->directory,
            'acceptedFileTypes' => $this->acceptedFileTypes,
        ]);
    }
}