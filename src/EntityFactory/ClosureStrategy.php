<?php

declare(strict_types=1);

namespace Butschster\EntityFaker\EntityFactory;

use Closure;

final class ClosureStrategy implements StrategyInterface
{
    public function __construct(
        private readonly Closure $closure
    ) {
    }

    public function create(string $class, array $data): object
    {
        return \call_user_func($this->closure, $class, $data);
    }
}
