<?php

declare(strict_types=1);

namespace Butschster\EntityFaker;

use Butschster\EntityFaker\EntityFactory\StrategyInterface;
use Closure;

interface EntityFactoryInterface
{
    public function withStrategy(StrategyInterface $strategy): self;

    /**
     * Creating an entity based on given class
     * @template T
     *
     * @param class-string<T> $class
     * @param array<non-empty-string, mixed> $data
     * @return T
     */
    public function create(string $class, array $data = []): object;

    /**
     * Place to persisting given entity
     * @param object $entity
     * @param array $options
     */
    public function store(object $entity, array $options = []): void;

    /**
     * Hydrate data to a given entity
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function hydrate(object $entity, array $data): object;

    /**
     * Callbacks to run before creating entities
     * @return array<Closure>
     */
    public function beforeCreationCallbacks(): array;

    /**
     * Callbacks to run after creating entities
     * @return array<Closure>
     */
    public function afterCreationCallbacks(): array;
}
