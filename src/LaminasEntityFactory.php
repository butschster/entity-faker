<?php
declare(strict_types=1);

namespace Butschster\EntityFaker;

use Laminas\Hydrator\HydratorInterface;
use ReflectionClass;

class LaminasEntityFactory extends AbstractEntityFactory
{
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
}
