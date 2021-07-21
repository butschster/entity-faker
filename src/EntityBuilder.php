<?php
declare(strict_types=1);

namespace Butschster\EntityFaker;

use Faker\Generator as Faker;
use InvalidArgumentException;

class EntityBuilder
{
    private Faker $faker;
    private EntityFactoryInterface $factory;

    /** The entity definitions in the container. */
    private array $definitions;
    /** The entity being built. */
    private string $class;
    /** The entity states. */
    private array $states = [];
    /** The entity after making callbacks. */
    private array $afterMaking = [];
    /** The entity after creating callbacks. */
    private array $afterCreating = [];
    /** The states to apply. */
    private array $activeStates = [];
    /** The number of models to build. */
    protected ?int $amount = null;

    public function __construct(
        EntityFactoryInterface $factory, Faker $faker,
        string $class, array $definitions, array $states,
        array $afterMaking, array $afterCreating
    )
    {
        $this->factory = $factory;
        $this->faker = $faker;
        $this->class = $class;
        $this->definitions = $definitions;
        $this->states = $states;
        $this->afterMaking = $afterMaking;
        $this->afterCreating = $afterCreating;
    }

    /**
     * Set the amount of entity you wish to create / make.
     * @param int|null $amount
     * @return $this
     */
    public function times(?int $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Export data to given directory
     * @param string $directory
     * @param bool $replaceIfExists
     * @return string
     * @throws \ReflectionException
     */
    public function export(string $directory, bool $replaceIfExists = true): string
    {
        $reflect = new \ReflectionClass($this->class);
        $filePath = $directory . DIRECTORY_SEPARATOR . $reflect->getShortName() . '.php';
        if (!$replaceIfExists && file_exists($filePath)) {
            return $filePath;
        }

        $data = $this->raw();
        if ($this->amount === null) {
            $data = [$data];
        }

        $array = var_export($data, true);
        $date = date('Y-m-d H:i:s');

        file_put_contents($filePath, <<<EOL
<?php
// $date
return $array;
EOL
        );

        return $filePath;
    }

    /**
     * Set the state to be applied to the entity.
     * @param string $state
     * @return $this
     */
    public function state(string $state)
    {
        return $this->states([$state]);
    }

    /**
     * Set the states to be applied to the entity.
     *
     * @param array $states
     * @return $this
     */
    public function states(array $states)
    {
        $this->activeStates = $states;

        return $this;
    }

    /**
     * Create a array of entities and persist them to the database.
     *
     * @param array $attributes
     * @return object|array
     */
    public function create(array $attributes = [])
    {
        $results = $this->make($attributes);

        if (is_object($results)) {
            $this->callCallbacks($this->factory->beforeCreationCallbacks());
            $this->store([$results]);
            $this->callCallbacks($this->factory->afterCreationCallbacks());
        } else {
            $this->callCallbacks($this->factory->beforeCreationCallbacks());
            $this->store($results);
            $this->callCallbacks($this->factory->afterCreationCallbacks());
        }

        return $results;
    }

    /**
     * Store entity.
     * @param array $results
     */
    protected function store(array $results): void
    {
        foreach ($results as $entity) {
            $this->factory->store($entity);
        }
    }

    /**
     * Create an array of entities.
     *
     * @param array $attributes
     * @return array|object
     */
    public function make(array $attributes = [])
    {
        if ($this->amount === null) {
            $instance = $this->makeInstance($attributes);
            $this->callAfterMaking([$instance]);

            return $instance;
        }

        if ($this->amount < 1) {
            return [];
        }

        $instances = array_map(function () use ($attributes) {
            return $this->makeInstance($attributes);
        }, range(1, $this->amount));

        $this->callAfterMaking($instances);

        return $instances;
    }

    /**
     * Create an array of raw attribute arrays.
     *
     * @param array $attributes
     * @return array
     */
    public function raw(array $attributes = []): array
    {
        if ($this->amount === null) {
            return $this->getRawAttributes($attributes);
        }

        if ($this->amount < 1) {
            return [];
        }

        return array_map(function () use ($attributes) {
            return $this->getRawAttributes($attributes);
        }, range(1, $this->amount));
    }

    /**
     * Get a raw attributes array for the entity.
     *
     * @param array $attributes
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected function getRawAttributes(array $attributes = []): array
    {
        if (!isset($this->definitions[$this->class])) {
            throw new InvalidArgumentException("Unable to locate factory for [{$this->class}].");
        }

        $definition = call_user_func(
            $this->definitions[$this->class],
            $this->faker, $attributes
        );

        return $this->expandAttributes(
            array_merge($this->applyStates($definition, $attributes), $attributes)
        );
    }

    /**
     * Make an instance of the entity with the given attributes.
     *
     * @param array $attributes
     * @return object
     */
    protected function makeInstance(array $attributes = []): object
    {
        $instance = $this->factory->create($this->class);
        $attributes = $this->getRawAttributes($attributes);

        return $this->factory->hydrate($instance, $attributes);
    }

    /**
     * Apply the active states to the entity definition array.
     *
     * @param array $definition
     * @param array $attributes
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function applyStates(array $definition, array $attributes = []): array
    {
        foreach ($this->activeStates as $state) {
            if (!isset($this->states[$this->class][$state])) {
                if ($this->stateHasAfterCallback($state)) {
                    continue;
                }

                throw new InvalidArgumentException("Unable to locate [{$state}] state for [{$this->class}].");
            }

            $definition = array_merge(
                $definition,
                $this->stateAttributes($state, $attributes)
            );
        }

        return $definition;
    }

    /**
     * Get the state attributes.
     *
     * @param string $state
     * @param array $attributes
     * @return array
     */
    protected function stateAttributes(string $state, array $attributes): array
    {
        $stateAttributes = $this->states[$this->class][$state];

        if (!is_callable($stateAttributes)) {
            return $stateAttributes;
        }

        return $stateAttributes($this->faker, $attributes);
    }

    /**
     * Expand all attributes to their underlying values.
     *
     * @param array $attributes
     * @return array
     */
    protected function expandAttributes(array $attributes): array
    {
        foreach ($attributes as &$attribute) {
            if (is_callable($attribute) && !is_string($attribute) && !is_array($attribute)) {
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
     *
     * @param array $entities
     * @return void
     */
    public function callAfterMaking(array $entities): void
    {
        $this->callAfter($this->afterMaking, $entities);
    }

    /**
     * Run after creating callbacks on an array of entities.
     *
     * @param array $entities
     * @return void
     */
    public function callAfterCreating(array $entities): void
    {
        $this->callAfter($this->afterCreating, $entities);
        $this->callAfter($this->factory->afterCreationCallbacks(), $entities);
    }

    /**
     * Call after callbacks for each entity and state.
     *
     * @param array $afterCallbacks
     * @param array $entities
     * @return void
     */
    protected function callAfter(array $afterCallbacks, array $entities): void
    {
        $states = array_merge(['default'], $this->activeStates);

        foreach ($entities as $entity) {
            foreach ($states as $state) {
                $this->callAfterCallbacks($afterCallbacks, $entity, $state);
            }
        }
    }

    /**
     * Call after callbacks for each entity and state.
     *
     * @param array $afterCallbacks
     * @param object $entity
     * @param string $state
     * @return void
     */
    protected function callAfterCallbacks(array $afterCallbacks, object $entity, string $state): void
    {
        if (!isset($afterCallbacks[$this->class][$state])) {
            return;
        }

        foreach ($afterCallbacks[$this->class][$state] as $callback) {
            $callback($entity, $this->faker);
        }
    }

    /**
     * Determine if the given state has an "after" callback.
     *
     * @param string $state
     * @return bool
     */
    protected function stateHasAfterCallback(string $state): bool
    {
        return isset($this->afterMaking[$this->class][$state]) ||
            isset($this->afterCreating[$this->class][$state]);
    }

    private function callCallbacks(array $callbacks): void
    {
        foreach ($callbacks as $callback) {
            $callback();
        }
    }
}
