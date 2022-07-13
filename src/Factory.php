<?php

declare(strict_types=1);

namespace Butschster\EntityFaker;

use Butschster\EntityFaker\EntityFactory\StrategyInterface;
use Butschster\EntityFaker\Seeds\InMemorySeedRepository;
use Butschster\EntityFaker\Seeds\SeedRepositoryInterface;
use Closure;
use Faker\Generator;

class Factory
{
    /**
     * The entity definitions in the container.
     */
    private array $definitions = [];
    /**
     * The registered entity states.
     * @var array<class-string, array<Closure>>
     */
    private array $states = [];
    /**
     * The registered after making callbacks.
     * @var array<class-string, array<Closure>>
     */
    private array $afterMaking = [];
    /**
     * The registered after creating callbacks.
     * @var array<class-string, array<Closure>>
     */
    private array $afterCreating = [];
    /** @var array<class-string, StrategyInterface> */
    private array $creationStrategy = [];

    public function __construct(
        private readonly EntityFactoryInterface $entityFactory,
        private readonly Generator $faker
    ) {
    }

    public function creationStrategy(string $class, StrategyInterface $strategy): self
    {
        $this->creationStrategy[$class] = $strategy;

        return $this;
    }

    /**
     * Define a class with a given set of attributes.
     * @param class-string $class
     */
    public function define(string $class, Closure $attributes): self
    {
        $this->definitions[$class] = $attributes;

        return $this;
    }

    /**
     * Define a state with a given set of attributes.
     * @param class-string $class
     */
    public function state(string $class, Closure $state): self
    {
        $this->states[$class][] = $state;

        return $this;
    }

    /**
     * Define a state with a given set of attributes.
     * @param class-string $class
     * @param array<Closure> $states
     * @return Factory
     */
    public function states(string $class, array $states): self
    {
        foreach ($states as $state) {
            $this->state($class, $state);
        }

        return $this;
    }

    /**
     * Define a callback to run after making a model.
     * @param class-string $class
     */
    public function afterMaking(string $class, callable $callback): self
    {
        $this->afterMaking[$class][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after creating a model.
     * @param class-string $class
     */
    public function afterCreating(string $class, callable $callback): self
    {
        $this->afterCreating[$class][] = $callback;

        return $this;
    }

    /**
     * Create a builder for the given entity.
     * @param class-string $class
     */
    public function of(string $class): EntityBuilder
    {
        if (! isset($this->definitions[$class])) {
            throw new \InvalidArgumentException(\sprintf('Unable to locate factory for [%s].', $class));
        }

        $factory = $this->entityFactory;

        if (isset($this->creationStrategy[$class])) {
            $factory = $factory->withStrategy($this->creationStrategy[$class]);
        }

        return new EntityBuilder(
            $factory,
            $this->faker,
            $class,
            $this->definitions[$class] ?? static fn() => [],
            $this->states[$class] ?? [],
            $this->afterMaking[$class] ?? [],
            $this->afterCreating[$class] ?? [],
        );
    }

    /**
     * Create an instance of the given entity
     * @param positive-int $times
     */
    public function make(int $times): SeedRepositoryInterface
    {
        $data = [];

        foreach ($this->definitions as $class => $definition) {
            $data[$class] = $this->of($class)->times($times)->make();
        }

        return new InMemorySeedRepository($data);
    }

    /**
     * Get the raw data.
     * @param positive-int $times
     */
    public function raw(int $times = 100): SeedRepositoryInterface
    {
        $data = [];

        foreach ($this->definitions as $class => $definition) {
            $data[$class] = $this->of($class)->times($times)->raw();
        }

        return new InMemorySeedRepository($data);
    }

    public function getEntityFactory(): EntityFactoryInterface
    {
        return $this->entityFactory;
    }
}
