<?php

namespace Vaneetjoshi\LaravelUtilities\Settings\Contracts;

interface GroupContract
{
    public function getName(): string;
    public function label(string $label): self;
    public function description(string $description): self;
    public function icon(string $icon): self;
    public function getIcon(): string;
    
    // Authorization Controls
    public function roles(string|array $roles): self;
    public function permissions(string|array $permissions): self;
    public function isAuthorized(mixed $user): bool;
    
    public function addFields(array $fields): self;
    
    /**
     * Retrieve fields, optionally filtered by user authorization.
     */
    public function getFields(mixed $user = null): array;
    
    /**
     * Retrieve validation rules, optionally filtered by user authorization.
     */
    public function getValidationRules(mixed $user = null): array;
    
    /**
     * Retrieve mapped validation attributes for the group.
     */
    public function getValidationAttributes(array $submittedData, mixed $user = null): array;
    
    public function toArray(): array;
}