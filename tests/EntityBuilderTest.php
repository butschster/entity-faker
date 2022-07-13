<?php

declare(strict_types=1);

namespace Butschster\Tests;

use Butschster\EntityFaker\EntityBuilder;
use Butschster\EntityFaker\EntityFactoryInterface;
use Faker\Generator;
use Laminas\Hydrator\ReflectionHydrator;
use Mockery as m;

class EntityBuilderTest extends TestCase
{
    private EntityBuilder $builder;
    /** @var EntityFactoryInterface|m\LegacyMockInterface|m\MockInterface */
    private m\MockInterface $entityFactory;
    private ReflectionHydrator $hydrator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hydrator = new ReflectionHydrator();
        $this->entityFactory = m::mock(EntityFactoryInterface::class);

        $this->builder = new EntityBuilder(
            factory: $this->entityFactory,
            faker: $this->createFaker(),
            class: EntityBuilderUser::class,
            definitions: function (Generator $faker, array $attributes) {
                return [
                    'id' => $faker->uuid,
                    'username' => $faker->unique()->userName,
                    'email' => $faker->unique()->email,
                ];
            },
            states: [
                static fn(Generator $faker, array $attributes) => ['username' => $faker->unique()->userName],
                static fn(Generator $faker, array $attributes) => ['email' => 'internal@site.com'],
            ]
        );
    }

    function test_creates_single_entity()
    {
        $user = new EntityBuilderUser();
        $this->entityFactory->shouldReceive('create')
            ->once()
            ->with(
                EntityBuilderUser::class,
                [
                    'id' => '0b13e52d-b058-32fb-8507-10dec634a07c',
                    'username' => 'lane65',
                    'email' => 'internal@site.com',
                ]
            )
            ->andReturn($user);

        $this->entityFactory->shouldReceive('store')->once()->with($user);
        $this->entityFactory->shouldReceive('beforeCreationCallbacks')->once();
        $this->entityFactory->shouldReceive('afterCreationCallbacks')->once();

        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(
            function (EntityBuilderUser $user, array $attributes) {
                return $this->hydrator->hydrate($attributes, $user);
            }
        );

        $user = $this->builder->create();

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->getId());
        $this->assertEquals('lane65', $user->getUsername());
        $this->assertEquals('internal@site.com', $user->getEmail());
    }

    function test_creates_single_entity_with_predefined_attributes()
    {
        $user = new EntityBuilderUser();
        $this->entityFactory->shouldReceive('create')
            ->once()
            ->with(
                EntityBuilderUser::class,
                [
                    'id' => 'hello_world',
                    'username' => 'lane65',
                    'email' => 'internal@site.com',
                ]
            )
            ->andReturn($user);
        $this->entityFactory->shouldReceive('store')->once()->with($user);
        $this->entityFactory->shouldReceive('beforeCreationCallbacks')->once();
        $this->entityFactory->shouldReceive('afterCreationCallbacks')->once();
        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(
            function (EntityBuilderUser $user, array $attributes) {
                return $this->hydrator->hydrate($attributes, $user);
            }
        );

        $user = $this->builder->create(['id' => 'hello_world']);

        $this->assertEquals('hello_world', $user->getId());
        $this->assertEquals('lane65', $user->getUsername());
        $this->assertEquals('internal@site.com', $user->getEmail());
    }

    function test_creates_multiply_entity()
    {
        $this->entityFactory->shouldReceive('create')
            ->times(3)
            ->withSomeOfArgs(EntityBuilderUser::class)
            ->andReturnUsing(
                function () {
                    return new EntityBuilderUser();
                }
            );

        $this->entityFactory->shouldReceive('store')->times(3);
        $this->entityFactory->shouldReceive('hydrate')->times(3)->andReturnUsing(
            function (EntityBuilderUser $user, array $attributes) {
                return $this->hydrator->hydrate($attributes, $user);
            }
        );
        $this->entityFactory->shouldReceive('beforeCreationCallbacks')->once();
        $this->entityFactory->shouldReceive('afterCreationCallbacks')->once();

        $users = $this->builder->times(3)->create();

        $this->assertCount(3, $users);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $users[0]->getId());
        $this->assertEquals('lane65', $users[0]->getUsername());
        $this->assertEquals('internal@site.com', $users[0]->getEmail());

        $this->assertEquals('34169cbf-c877-3589-b81c-fadba6ca3c26', $users[1]->getId());
        $this->assertEquals('mwolf', $users[1]->getUsername());
        $this->assertEquals('internal@site.com', $users[1]->getEmail());

        $this->assertEquals('d38fd91b-5e82-37da-81f2-43db057d9196', $users[2]->getId());
        $this->assertEquals('celia68', $users[2]->getUsername());
        $this->assertEquals('internal@site.com', $users[2]->getEmail());
    }

    function test_creates_multiply_entity_with_predefined_attributes()
    {
        $this->entityFactory->shouldReceive('create')->times(3)
            ->withSomeOfArgs(EntityBuilderUser::class)
            ->andReturnUsing(
                function () {
                    return new EntityBuilderUser();
                }
            );
        $this->entityFactory->shouldReceive('store')->times(3);
        $this->entityFactory->shouldReceive('hydrate')->times(3)->andReturnUsing(
            function (EntityBuilderUser $user, array $attributes) {
                return $this->hydrator->hydrate($attributes, $user);
            }
        );
        $this->entityFactory->shouldReceive('beforeCreationCallbacks')->once();
        $this->entityFactory->shouldReceive('afterCreationCallbacks')->once();

        $users = $this->builder->times(3)->create(['username' => 'admin']);

        $this->assertCount(3, $users);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $users[0]->getId());
        $this->assertEquals('admin', $users[0]->getUsername());
        $this->assertEquals('internal@site.com', $users[0]->getEmail());

        $this->assertEquals('34169cbf-c877-3589-b81c-fadba6ca3c26', $users[1]->getId());
        $this->assertEquals('admin', $users[1]->getUsername());
        $this->assertEquals('internal@site.com', $users[1]->getEmail());

        $this->assertEquals('d38fd91b-5e82-37da-81f2-43db057d9196', $users[2]->getId());
        $this->assertEquals('admin', $users[2]->getUsername());
        $this->assertEquals('internal@site.com', $users[2]->getEmail());
    }

    function test_makes_single_entity()
    {
        $user = new EntityBuilderUser();
        $this->entityFactory->shouldReceive('create')
            ->once()
            ->withSomeOfArgs(EntityBuilderUser::class)
            ->andReturn($user);

        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(
            function (EntityBuilderUser $user, array $attributes) {
                return $this->hydrator->hydrate($attributes, $user);
            }
        );

        $user = $this->builder->make();

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->getId());
        $this->assertEquals('lane65', $user->getUsername());
        $this->assertEquals('internal@site.com', $user->getEmail());
    }

    function test_makes_single_entity_with_predefined_attributes()
    {
        $user = new EntityBuilderUser();
        $this->entityFactory->shouldReceive('create')
            ->once()
            ->withSomeOfArgs(EntityBuilderUser::class)
            ->andReturn($user);
        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(
            function (EntityBuilderUser $user, array $attributes) {
                return $this->hydrator->hydrate($attributes, $user);
            }
        );

        $user = $this->builder->make(['email' => 'test@site.com']);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->getId());
        $this->assertEquals('lane65', $user->getUsername());
        $this->assertEquals('test@site.com', $user->getEmail());
    }

    function test_makes_multiply_entity()
    {
        $this->entityFactory->shouldReceive('create')
            ->times(3)
            ->withSomeOfArgs(EntityBuilderUser::class)
            ->andReturnUsing(
                function () {
                    return new EntityBuilderUser();
                }
            );
        $this->entityFactory->shouldReceive('hydrate')->times(3)->andReturnUsing(
            function (EntityBuilderUser $user, array $attributes) {
                return $this->hydrator->hydrate($attributes, $user);
            }
        );

        $users = $this->builder->times(3)->make();

        $this->assertCount(3, $users);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $users[0]->getId());
        $this->assertEquals('lane65', $users[0]->getUsername());
        $this->assertEquals('internal@site.com', $users[0]->getEmail());

        $this->assertEquals('34169cbf-c877-3589-b81c-fadba6ca3c26', $users[1]->getId());
        $this->assertEquals('mwolf', $users[1]->getUsername());
        $this->assertEquals('internal@site.com', $users[1]->getEmail());

        $this->assertEquals('d38fd91b-5e82-37da-81f2-43db057d9196', $users[2]->getId());
        $this->assertEquals('celia68', $users[2]->getUsername());
        $this->assertEquals('internal@site.com', $users[2]->getEmail());
    }

    function test_makes_multiply_entity_with_predefined_attributes()
    {
        $this->entityFactory->shouldReceive('create')
            ->times(3)
            ->withSomeOfArgs(EntityBuilderUser::class)->andReturnUsing(
                function () {
                    return new EntityBuilderUser();
                }
            );

        $this->entityFactory->shouldReceive('hydrate')->times(3)->andReturnUsing(
            function (EntityBuilderUser $user, array $attributes) {
                return $this->hydrator->hydrate($attributes, $user);
            }
        );

        $users = $this->builder->times(3)->make(['email' => 'test@site.com']);

        $this->assertCount(3, $users);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $users[0]->getId());
        $this->assertEquals('lane65', $users[0]->getUsername());
        $this->assertEquals('test@site.com', $users[0]->getEmail());

        $this->assertEquals('34169cbf-c877-3589-b81c-fadba6ca3c26', $users[1]->getId());
        $this->assertEquals('mwolf', $users[1]->getUsername());
        $this->assertEquals('test@site.com', $users[1]->getEmail());

        $this->assertEquals('d38fd91b-5e82-37da-81f2-43db057d9196', $users[2]->getId());
        $this->assertEquals('celia68', $users[2]->getUsername());
        $this->assertEquals('test@site.com', $users[2]->getEmail());
    }

    function test_gets_raw_attributes_for_single_entity()
    {
        $this->assertEquals([
            'id' => '0b13e52d-b058-32fb-8507-10dec634a07c',
            'username' => 'lane65',
            'email' => 'internal@site.com',
        ], $this->builder->raw());
    }

    function test_gets_raw_attributes_for_multiply_entities()
    {
        $this->assertEquals([
            [
                'id' => '0b13e52d-b058-32fb-8507-10dec634a07c',
                'username' => 'lane65',
                'email' => 'internal@site.com',
            ],
            [
                'id' => '34169cbf-c877-3589-b81c-fadba6ca3c26',
                'username' => 'mwolf',
                'email' => 'internal@site.com',
            ],
            [
                'id' => 'd38fd91b-5e82-37da-81f2-43db057d9196',
                'username' => 'celia68',
                'email' => 'internal@site.com',
            ],
        ], $this->builder->times(3)->raw());
    }
}


class EntityBuilderUser
{
    private string $id;
    private string $username;
    private string $email;

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
