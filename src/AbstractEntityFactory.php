<?php

declare(strict_types=1);

namespace Butschster\EntityFaker;

use Butschster\EntityFaker\EntityFactory\StrategyInterface;

abstract class AbstractEntityFactory implements EntityFactoryInterface
{
    /** @var array<callable> */
    private array $afterCreation = [];
    /** @var array<callable> */
    private array $beforeCreation = [];

    public function __construct(
        private StrategyInterface $strategy
    ) {
    }

    /** @inheritDoc */
    public function create(string $class, array $data = []): object
    {
        return $this->strategy->create($class, $data);
    }

    /** @inheritDoc */
    public function store(object $entity, array $options = []): void
    {
        // do noting
    }

    public function withStrategy(StrategyInterface $strategy): self
    {
        $self = clone $this;
        $self->strategy = $strategy;

        return $self;
    }

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
