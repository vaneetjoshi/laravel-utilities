<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Contracts;

use Illuminate\View\View;

interface FieldContract
{
    public function getName(): string;
    public function getRules(string $prefix = ''): array;
    
    public function getValidationAttributes(array $submittedData, string $prefix = ''): array;
    
    public function toArray(): array;
    
    /**
     * Render the field. 
     * $nameOverride is used by ArrayField to dynamically scope nested inputs (e.g. parent[0][child]).
     */
    public function render(mixed $currentValue = null, ?string $nameOverride = null): View|string;
    
    public function dependsOn(string $field, mixed $value = '1'): static;
    public function getDependsOnField(): ?string;
    public function getDependsOnValue(): mixed;

    public function roles(string|array $roles): static;
    public function permissions(string|array $permissions): static;
    public function isAuthorized(mixed $user): bool;
}