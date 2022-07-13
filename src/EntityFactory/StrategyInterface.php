<?php

declare(strict_types=1);

namespace Butschster\EntityFaker\EntityFactory;

interface StrategyInterface
{
    /**
     * Creating an entity based on given class
     * @template T
     *
     * @param class-string<T> $class
     * @param array<non-empty-string, mixed> $data
     * @return T
     */
    public function create(string $class, array $data): object;
}
