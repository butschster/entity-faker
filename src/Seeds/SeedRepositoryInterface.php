<?php
declare(strict_types=1);

namespace Butschster\EntityFaker\Seeds;

interface SeedRepositoryInterface
{
    /**
     * Get seed for given entity
     *
     * @param string $entity
     * @return Seeds
     */
    public function get(string $entity): Seeds;

    public function toArray(): array;
}
