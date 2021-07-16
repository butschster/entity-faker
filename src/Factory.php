<?php
declare(strict_types=1);

namespace Butschster\EntityFaker;

use Faker\Generator;

class Factory
{
    /** The entity definitions in the container. */
    protected array $definitions = [];
    /** The registered entity states. */
    protected array $states = [];
    /** The registered after making callbacks. */
    protected array $afterMaking = [];
    /** The registered after creating callbacks. */
    protected array $afterCreating = [];
    /** The Faker instance for the builder. */
    protected Generator $faker;

    private EntityFactoryInterface $entityFactory;

    /**
     * Create a new factory instance.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param Generator $faker
     */
    public function __construct(EntityFactoryInterface $entityFactory, Generator $faker)
    {
        $this->faker = $faker;
        $this->entityFactory = $entityFactory;
    }

    /**
     * Define a class with a given set of attributes.
     *
     * @param string $class
     * @param callable $attributes
     * @return $this
     */
    public function define(string $class, callable $attributes)
    {
        $this->definitions[$class] = $attributes;

        return $this;
    }

    /**
     * Define a state with a given set of attributes.
     *
     * @param string $class
     * @param string $state
     * @param callable|array $attributes
     * @return $this
     */
    public function state(string $class, string $state, $attributes)
    {
        $this->states[$class][$state] = $attributes;

        return $this;
    }

    /**
     * Define a callback to run after making a model.
     *
     * @param string $class
     * @param callable $callback
     * @param string $name
     * @return $this
     */
    public function afterMaking(string $class, callable $callback, string $name = 'default')
    {
        $this->afterMaking[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after making a model with given state.
     *
     * @param string $class
     * @param string $state
     * @param callable $callback
     * @return $this
     */
    public function afterMakingState(string $class, string $state, callable $callback)
    {
        return $this->afterMaking($class, $callback, $state);
    }

    /**
     * Define a callback to run after creating a model.
     *
     * @param string $class
     * @param callable $callback
     * @param string $name
     * @return $this
     */
    public function afterCreating(string $class, callable $callback, string $name = 'default')
    {
        $this->afterCreating[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after creating a model with given state.
     *
     * @param string $class
     * @param string $state
     * @param callable $callback
     * @return $this
     */
    public function afterCreatingState(string $class, string $state, callable $callback)
    {
        return $this->afterCreating($class, $callback, $state);
    }

    /**
     * Create a builder for the given entity.
     *
     * @param string $class
     * @return EntityBuilder
     */
    public function of(string $class): EntityBuilder
    {
        return new EntityBuilder(
            $this->entityFactory,
            $this->faker,
            $class,
            $this->definitions, $this->states,
            $this->afterMaking, $this->afterCreating,
        );
    }

    /**
     * Create an instance of the given entity and persist it
     * @param string $class
     * @param array $attributes
     * @param int|null $times
     * @return object
     */
    public function create(string $class, array $attributes = [], ?int $times = null): object
    {
        return $this->of($class)->times($times)->create($attributes);
    }

    /**
     * Create an instance of the given entity
     * @param string $class
     * @param array $attributes
     * @param int|null $times
     * @return object
     */
    public function make(string $class, array $attributes = [], ?int $times = null): object
    {
        return $this->of($class)->times($times)->make($attributes);
    }

    /**
     * Get the raw attribute array for a given model.
     *
     * @param string $class
     * @param array $attributes
     * @return array
     */
    public function raw(string $class, array $attributes = []): array
    {
        return array_merge(
            call_user_func($this->definitions[$class], $this->faker), $attributes
        );
    }

    /**
     * Export generated data to given directory
     *
     * @param string $directory
     * @param int $times
     * @param bool $replaceIfExists
     * @return array<string, string> Array of generated files
     * @throws \ReflectionException
     */
    public function export(string $directory, int $times = 100, bool $replaceIfExists = true): array
    {
        $files = [];
        foreach ($this->definitions as $class => $definition) {
            $files[$class] = $this->of($class)->times($times)->export($directory, $replaceIfExists);
        }

        return $files;
    }

    public function getEntityFactory(): EntityFactoryInterface
    {
        return $this->entityFactory;
    }
}
