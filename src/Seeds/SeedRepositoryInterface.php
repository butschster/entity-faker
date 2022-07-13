<?php
declare(strict_types=1);

namespace Butschster\EntityFaker\Seeds;

interface SeedRepositoryInterface
{
    /**
     * Get seed for given entity
     * @param class-string $entity
     */
    public function get(string $entity): Seeds;

    public function toArray(): array;
}
