<?php

declare(strict_types=1);

namespace Butschster\EntityFaker;

interface EntityFactoryInterface
{
    public function create(string $class): object;

    public function store(object $entity): void;

    public function hydrate(object $entity, array $data): object;
}
