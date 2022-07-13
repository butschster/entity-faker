<?php

declare(strict_types=1);

namespace Butschster\EntityFaker;

use Closure;
use Faker\Generator as Faker;
use InvalidArgumentException;

final class EntityBuilder
{
    /** The number of models to build. */
    protected ?int $amount = null;

    public function __construct(
        private readonly EntityFactoryInterface $factory,
        private readonly Faker $faker,
        /**
         * The entity class.
         * @var class-string
         */
        private readonly string $class,
        /**
         * The entity definitions in the container.
         */
        private readonly Closure $definitions,
        /**
         * The entity states.
         * @var array<Closure>
         */
        private readonly array $states = [],
        /**
         * The entity after making callbacks.
         * @var array<Closure>
         */
        private readonly array $afterMaking = [],
        /**
         * The entity after creating callbacks.
         * @var array<Closure>
         */
        private readonly array $afterCreating = []
    ) {
    }

    /**
     * Set the amount of entity you wish to create / make.
     * @param positive-int $amount
     */
    public function times(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Create an array of entities and persist them to the database.
     *
     * @return object|array<object>
     */
    public function create(array $attributes = []): object|array
    {
        $results = $this->make($attributes);

        $this->callCallbacks($this->factory->beforeCreationCallbacks());
        $this->store(\is_object($results) ? [$results] : $results);
        $this->callCallbacks($this->factory->afterCreationCallbacks());

        return $results;
    }

    /**
     * Store entity.
     */
    protected function store(array $results): void
    {
        foreach ($results as $entity) {
            $this->factory->store($entity);
        }
    }

    /**
     * Create an array of entities.
     * @return object|array<object>
     */
    public function make(array $attributes = []): object|array
    {
        if ($this->amount === null) {
            $instance = $this->makeInstance($attributes);
            $this->callAfterMaking([$instance]);

            return $instance;
        }

        if ($this->amount < 1) {
            return [];
        }

        $instances = \array_map(function () use ($attributes) {
            return $this->makeInstance($attributes);
        }, \range(1, $this->amount));

        $this->callAfterMaking($instances);

        return $instances;
    }

    /**
     * Create an array of raw attribute arrays.
     */
    public function raw(array $attributes = []): array
    {
        if ($this->amount === null) {
            return $this->getRawAttributes($attributes);
        }

        if ($this->amount < 1) {
            return [];
        }

        return \array_map(function () use ($attributes) {
            return $this->getRawAttributes($attributes);
        }, \range(1, $this->amount));
    }

    /**
     * Get a raw attributes array for the entity.
     *
     * @throws InvalidArgumentException
     */
    protected function getRawAttributes(array $attributes = []): array
    {
        $definition = \call_user_func(
            $this->definitions,
            $this->faker,
            $attributes
        );

        return $this->expandAttributes(
            \array_merge($this->applyStates($definition, $attributes), $attributes)
        );
    }

    /**
     * Make an instance of the entity with the given attributes.
     */
    protected function makeInstance(array $attributes = []): object
    {
        $attributes = $this->getRawAttributes($attributes);
        $instance = $this->factory->create($this->class, $attributes);

        return $this->factory->hydrate($instance, $attributes);
    }

    /**
     * Apply the active states to the entity definition array.
     *
     * @throws \InvalidArgumentException
     */
    protected function applyStates(array $definition, array $attributes = []): array
    {
        foreach ($this->states as $state) {
            $definition = \array_merge(
                $definition,
                $state($this->faker, $attributes)
            );
        }

        return $definition;
    }

    /**
     * Expand all attributes to their underlying values.
     */
    protected function expandAttributes(array $attributes): array
    {
        foreach ($attributes as &$attribute) {
            if (\is_callable($attribute) && ! \is_string($attribute) && ! \is_array($attribute)) {
                $attribute = $attribute($attributes);
            }

            if ($attribute instanceof static) {
                $attribute = $attribute->create()->getKey();
            }
        }

        return $attributes;
    }

    /**
     * Run after making callbacks on an array of entities.
     */
    public function callAfterMaking(array $entities): void
    {
        $this->callAfter($this->afterMaking, $entities);
    }

    /**
     * Run after creating callbacks on an array of entities.
     */
    public function callAfterCreating(array $entities): void
    {
        $this->callAfter($this->afterCreating, $entities);
        $this->callAfter($this->factory->afterCreationCallbacks(), $entities);
    }

    /**
     * Call after callbacks for each entity and state.
     */
    protected function callAfter(array $afterCallbacks, array $entities): void
    {
        foreach ($entities as $entity) {
            $this->callAfterCallbacks($afterCallbacks, $entity);
        }
    }

    /**
     * Call after callbacks for each entity and state.
     */
    protected function callAfterCallbacks(array $afterCallbacks, object $entity): void
    {
        if (! isset($afterCallbacks)) {
            return;
        }

        foreach ($afterCallbacks as $callback) {
            $callback($entity, $this->faker);
        }
    }

    private function callCallbacks(array $callbacks): void
    {
        foreach ($callbacks as $callback) {
            $callback();
        }
    }
}
