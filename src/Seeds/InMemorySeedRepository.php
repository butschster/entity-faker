<?php
declare(strict_types=1);

namespace Butschster\EntityFaker\Seeds;

class InMemorySeedRepository implements SeedRepositoryInterface
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get seed for given entity
     *
     * @param string $entity
     * @return Seeds
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
