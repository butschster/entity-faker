<?php

declare(strict_types=1);

namespace Butschster\EntityFaker\EntityFactory;

use ReflectionClass;

final class InstanceWithoutConstructorStrategy implements StrategyInterface
{
    public function create(string $class, array $data): object
    {
        $reflection = new ReflectionClass($class);

        return $reflection->newInstanceWithoutConstructor();
    }
}
