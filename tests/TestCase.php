<?php
declare(strict_types=1);

namespace Butschster\Tests;

use Faker\Generator;
use Mockery as m;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    protected function createFaker(): Generator
    {
        $faker = \Faker\Factory::create();
        $faker->seed(1);

        return $faker;
    }

    protected function assertUninitializedProperty(object $object, string $property)
    {
        $refl = new \ReflectionClass($object);
        $prop = $refl->getProperty($property);
        $prop->setAccessible(true);

        $this->assertFalse($prop->isInitialized($object), "Property [{$property}] is initialized");
    }
}
