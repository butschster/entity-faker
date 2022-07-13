<?php
declare(strict_types=1);

namespace Butschster\EntityFaker\Seeds;

class InMemorySeedRepository implements SeedRepositoryInterface
{
    public function __construct(
        private readonly array $data
    ) {
    }

    /**
     * Get seed for given entity
     * @param class-string $entity
     */
    public function get(string $entity): Seeds
    {
        if (!isset($this->data[$entity])) {
            throw new \RuntimeException("Seeds for entity [$entity] not found.");
        }

        return new Seeds($entity, $this->data[$entity]);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
