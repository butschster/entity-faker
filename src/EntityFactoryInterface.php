<?php

declare(strict_types=1);

namespace Butschster\EntityFaker;

interface EntityFactoryInterface
{
    /**
     * Creating an entity based on given class
     * @param string $class
     * @return object
     */
    public function create(string $class): object;

    /**
     * Place to persisting given entity
     * @param object $entity
     */
    public function store(object $entity): void;

    /**
     * Hydrate data to a given entity
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function hydrate(object $entity, array $data): object;

    /**
     * Callbacks to run after creating entities
     * @return array
     */
    public function afterCreationCallbacks(): array;
}
