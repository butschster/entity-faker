<?php
declare(strict_types=1);

namespace Butschster\EntityFaker;

use Butschster\EntityFaker\Seeds\FileSeedRepository;
use Butschster\EntityFaker\Seeds\InMemorySeedRepository;
use Butschster\EntityFaker\Seeds\SeedRepositoryInterface;
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
     * Create an instance of the given entity
     * @param int $times
     * @return SeedRepositoryInterface
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
     *
     * @param int $times
     * @return SeedRepositoryInterface
     */
    public function raw(int $times = 100): SeedRepositoryInterface
    {
        $data = [];

        foreach ($this->definitions as $class => $definition) {
            $data[$class] = $this->of($class)->times($times)->raw();
        }

        return new InMemorySeedRepository($data);
    }

    /**
     * Export generated data to given directory
     *
     * @param string $directory
     * @param int $times
     * @param bool $replaceIfExists
     * @return SeedRepositoryInterface
     * @throws \ReflectionException
     */
    public function export(string $directory, int $times = 100, bool $replaceIfExists = true): SeedRepositoryInterface
    {
        $files = [];
        foreach ($this->definitions as $class => $definition) {
            $files[$class] = $this->of($class)->times($times)->export($directory, $replaceIfExists);
        }

        return new FileSeedRepository($files);
    }

    public function getEntityFactory(): EntityFactoryInterface
    {
        return $this->entityFactory;
    }
}
