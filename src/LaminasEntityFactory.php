<?php
declare(strict_types=1);

namespace Butschster\EntityFaker;

use Laminas\Hydrator\HydratorInterface;
use ReflectionClass;

class LaminasEntityFactory implements EntityFactoryInterface
{
    private array $afterCreation = [];
    private array $beforeCreation = [];

    private HydratorInterface $hydrator;

    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    public function create(string $class): object
    {
        $reflection = new ReflectionClass($class);

        return $reflection->newInstanceWithoutConstructor();
    }

    public function store(object $entity, array $options = []): void
    {
        // do noting
    }

    public function hydrate(object $entity, array $data): object
    {
        return $this->hydrator->hydrate($data, $entity);
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
}
