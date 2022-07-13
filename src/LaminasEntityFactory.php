<?php

declare(strict_types=1);

namespace Butschster\EntityFaker;

use Butschster\EntityFaker\EntityFactory\StrategyInterface;
use Laminas\Hydrator\HydratorInterface;

class LaminasEntityFactory extends AbstractEntityFactory
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        StrategyInterface $strategy,
    ) {
        parent::__construct($strategy);
    }

    /** @inheritDoc */
    public function hydrate(object $entity, array $data): object
    {
        return $this->hydrator->hydrate($data, $entity);
    }
}
