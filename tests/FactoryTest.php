<?php
declare(strict_types=1);

namespace Butschster\Tests;

use Butschster\EntityFaker\EntityBuilder;
use Butschster\EntityFaker\Factory;
use Butschster\EntityFaker\Seeds\FileSeedRepository;
use Butschster\EntityFaker\Seeds\InMemorySeedRepository;
use Faker\Generator;
use InvalidArgumentException;
use Mockery as m;
use Butschster\EntityFaker\EntityFactoryInterface;

final class FactoryTest extends TestCase
{
    private Factory $factory;
    private m\MockInterface $entityFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityFactory = m::mock(EntityFactoryInterface::class);

        $this->factory = new Factory(
            $this->entityFactory, $this->createFaker()
        );

        $this->factory->define(FactoryTestUser::class, static function (Generator $faker) {
            return [
                'id' => $faker->uuid,
                'username' => $faker->userName
            ];
        });

        $this->factory->define(FactoryTestComment::class, static function (Generator $faker) {
            return [
                'id' => $faker->uuid,
                'text' => $faker->text(30)
            ];
        });
    }

    function test_gets_entity_builder()
    {
        $this->assertInstanceOf(
            EntityBuilder::class,
            $this->factory->of(FactoryTestUser::class)
        );
    }

    function test_entity_for_defined_class_should_be_created()
    {
        $object = new FactoryTestUser;
        $this->entityFactory->shouldReceive('create')
            ->once()
            ->with(FactoryTestUser::class, ['id' => '0b13e52d-b058-32fb-8507-10dec634a07c', 'username' => 'zetta86'])
            ->andReturn($object);

        $this->entityFactory->shouldReceive('hydrate')
            ->once()
            ->andReturnUsing(function (object $user, array $data) {
                foreach ($data as $key => $value) {
                    $user->{$key} = $value;
                }

                return $user;
            });

        $this->entityFactory->shouldReceive('beforeCreationCallbacks')->once();
        $this->entityFactory->shouldReceive('afterCreationCallbacks')->once();
        $this->entityFactory->shouldReceive('store')->once($object);

        $user = $this->factory->of(FactoryTestUser::class)->create();

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->id);
        $this->assertEquals('zetta86', $user->username);
    }

    function test_entity_for_defined_class_should_be_made()
    {
        $object = new FactoryTestUser;
        $this->entityFactory->shouldReceive('create')
            ->once()
            ->with(FactoryTestUser::class, ['id' => '0b13e52d-b058-32fb-8507-10dec634a07c', 'username' => 'zetta86'])
            ->andReturn($object);

        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(function (object $user, array $data) {
            foreach ($data as $key => $value) {
                $user->{$key} = $value;
            }

            return $user;
        });

        $user = $this->factory->of(FactoryTestUser::class)->make();

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->id);
        $this->assertEquals('zetta86', $user->username);
    }

    function test_not_defined_class_should_throw_an_exception_when_making()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Unable to locate factory for [test].');

        $this->factory->of('test')->make();
    }

    function test_defined_class_should_generate_fake_raw_data()
    {
        $this->assertEquals([
            'id' => '0b13e52d-b058-32fb-8507-10dec634a07c',
            'username' => 'zetta86',
        ], $this->factory->of(FactoryTestUser::class)->raw());
    }

    function test_objects_should_be_generated_for_defined_class()
    {
        $seeds = $this->factory->raw(2);

        $this->assertInstanceOf(InMemorySeedRepository::class, $seeds);

        $this->assertCount(2, $userSeed = $seeds->get(FactoryTestUser::class));
        $this->assertCount(2, $commentSeed = $seeds->get(FactoryTestComment::class));

        $this->assertEquals([
            'id' => '0b13e52d-b058-32fb-8507-10dec634a07c',
            'username' => 'zetta86',
        ], $userSeed->first());

        $this->assertEquals([
            'id' => '3167f744-197b-3c9b-9419-71e5daf2ea18',
            'text' => 'Culpa ut ab voluptas sed a.'
        ], $commentSeed->first());
    }

    function test_raw_data_should_be_generated_for_defined_class()
    {
        $this->entityFactory->shouldReceive('create')
            ->times(4)->andReturnUsing(function (string $class) {
                return new $class;
            });

        $this->entityFactory->shouldReceive('hydrate')->times(4)->andReturnUsing(function (object $entity, array $data) {
            foreach ($data as $key => $value) {
                $entity->{$key} = $value;
            }

            return $entity;
        });

        $seeds = $this->factory->make(2);

        $this->assertInstanceOf(InMemorySeedRepository::class, $seeds);

        $this->assertCount(2, $userSeed = $seeds->get(FactoryTestUser::class));
        $this->assertCount(2, $commentSeed = $seeds->get(FactoryTestComment::class));

        $user = $userSeed->first();
        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->id);
        $this->assertEquals('zetta86', $user->username);

        $comment = $commentSeed->first();

        $this->assertEquals('3167f744-197b-3c9b-9419-71e5daf2ea18', $comment->id);
        $this->assertEquals('Culpa ut ab voluptas sed a.', $comment->text);
    }
}

class FactoryTestUser
{
    public string $id;
    public string $username;
}

class FactoryTestComment
{
    public string $id;
    public string $text;
}
