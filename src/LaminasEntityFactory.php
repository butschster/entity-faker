<?php
declare(strict_types=1);

namespace Butschster\EntityFaker;

use Laminas\Hydrator\HydratorInterface;
use ReflectionClass;

class LaminasEntityFactory implements EntityFactoryInterface
{
    private array $callbacks = [];
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

    public function store(object $entity): void
    {
        // do noting
    }

    public function hydrate(object $entity, array $data): object
    {
        return $this->hydrator->hydrate($data, $entity);
    }

    /**
     * Add a callback to run after creating an entity or array of entities.
     * @param callable $callback
     */
    public function afterCreation(callable $callback): void
    {
        $this->callbacks[] = $callback;
    }

    public function afterCreationCallbacks(): array
    {
        return [];
    }
}
