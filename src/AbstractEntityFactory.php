<?php

declare(strict_types=1);

namespace Butschster\EntityFaker;

abstract class AbstractEntityFactory implements EntityFactoryInterface
{
    private array $afterCreation = [];
    private array $beforeCreation = [];

    /**
     * Add a callback to run after creating an entity or array of entities.
     * @param callable $callback
     */
    public function afterCreation(callable $callback): void
    {
        $this->afterCreation[] = $callback;
    }

    public function afterCreationCallbacks(): array
    {
        return $this->afterCreation;
    }

    /**
     * Add a callback to run before creating an entity or array of entities.
     * @param callable $callback
     */
    public function beforeCreation(callable $callback): void
    {
        $this->beforeCreation[] = $callback;
    }

    public function beforeCreationCallbacks(): array
    {
        return $this->beforeCreation;
    }
}
