<?php
declare(strict_types=1);

namespace Butschster\EntityFaker\Seeds;

class FileSeedRepository implements SeedRepositoryInterface
{
    private array $data;
    private array $classMap;

    public function __construct(array $classMap)
    {
        $this->classMap = $classMap;
    }

    /**
     * Get seed for given entity
     *
     * @param string $entity
     * @return Seeds
     */
    public function get(string $entity): Seeds
    {
        if (!isset($this->classMap[$entity])) {
            throw new \RuntimeException("Seeds for entity [$entity] not found.");
        }

        if (!isset($this->data[$entity])) {
            $this->data[$entity] = include_once $this->classMap[$entity];
        }

        return new Seeds($entity, $this->data[$entity]);
    }

    public function toArray(): array
    {
        foreach ($this->classMap as $class => $filePath) {
            if (!isset($this->data[$class])) {
                $this->data[$class] = include_once $filePath;
            }
        }

        return $this->data;
    }
}
