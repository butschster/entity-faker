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

        $faker = \Faker\Factory::create();
        $faker->seed(1);

        $this->hydrator = new ReflectionHydrator();
        $this->entityFactory = m::mock(EntityFactoryInterface::class);

        $this->builder = new EntityBuilder(
            $this->entityFactory,
            $faker, EntityBuilderUser::class,
            [
                EntityBuilderUser::class => function (Generator $faker, array $attributes) {
                    return [
                        'id' => $faker->uuid,
                        'username' => $faker->unique()->userName,
                        'email' => $faker->unique()->email
                    ];
                }
            ], [], [], []
        );
    }

    function test_creates_single_entity()
    {
        $user = new EntityBuilderUser();
        $this->entityFactory->shouldReceive('create')->once()->with(EntityBuilderUser::class)->andReturn($user);
        $this->entityFactory->shouldReceive('store')->once()->with($user);
        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(function (EntityBuilderUser $user, array $attributes) {
            return $this->hydrator->hydrate($attributes, $user);
        });

        $user = $this->builder->create();

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->getId());
        $this->assertEquals('zetta86', $user->getUsername());
        $this->assertEquals('tsteuber@hotmail.com', $user->getEmail());
    }

    function test_creates_single_entity_with_predefined_attributes()
    {
        $user = new EntityBuilderUser();
        $this->entityFactory->shouldReceive('create')->once()->with(EntityBuilderUser::class)->andReturn($user);
        $this->entityFactory->shouldReceive('store')->once()->with($user);
        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(function (EntityBuilderUser $user, array $attributes) {
            return $this->hydrator->hydrate($attributes, $user);
        });

        $user = $this->builder->create(['id' => 'hello_world']);

        $this->assertEquals('hello_world', $user->getId());
        $this->assertEquals('zetta86', $user->getUsername());
        $this->assertEquals('tsteuber@hotmail.com', $user->getEmail());
    }

    function test_creates_multiply_entity()
    {
        $this->entityFactory->shouldReceive('create')->times(3)->with(EntityBuilderUser::class)->andReturnUsing(function () {
            return new EntityBuilderUser();
        });
        $this->entityFactory->shouldReceive('store')->times(3);
        $this->entityFactory->shouldReceive('hydrate')->times(3)->andReturnUsing(function (EntityBuilderUser $user, array $attributes) {
            return $this->hydrator->hydrate($attributes, $user);
        });

        $users = $this->builder->times(3)->create();

        $this->assertCount(3, $users);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $users[0]->getId());
        $this->assertEquals('zetta86', $users[0]->getUsername());
        $this->assertEquals('tsteuber@hotmail.com', $users[0]->getEmail());

        $this->assertEquals('3167f744-197b-3c9b-9419-71e5daf2ea18', $users[1]->getId());
        $this->assertEquals('erna39', $users[1]->getUsername());
        $this->assertEquals('herminia.hahn@gmail.com', $users[1]->getEmail());

        $this->assertEquals('a9c69793-81e3-31c5-b8ad-8d2282c7dfdb', $users[2]->getId());
        $this->assertEquals('nikki97', $users[2]->getUsername());
        $this->assertEquals('heloise.littel@kiehn.com', $users[2]->getEmail());
    }

    function test_creates_multiply_entity_with_predefined_attributes()
    {
        $this->entityFactory->shouldReceive('create')->times(3)->with(EntityBuilderUser::class)->andReturnUsing(function () {
            return new EntityBuilderUser();
        });
        $this->entityFactory->shouldReceive('store')->times(3);
        $this->entityFactory->shouldReceive('hydrate')->times(3)->andReturnUsing(function (EntityBuilderUser $user, array $attributes) {
            return $this->hydrator->hydrate($attributes, $user);
        });

        $users = $this->builder->times(3)->create(['username' => 'admin']);

        $this->assertCount(3, $users);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $users[0]->getId());
        $this->assertEquals('admin', $users[0]->getUsername());
        $this->assertEquals('tsteuber@hotmail.com', $users[0]->getEmail());

        $this->assertEquals('3167f744-197b-3c9b-9419-71e5daf2ea18', $users[1]->getId());
        $this->assertEquals('admin', $users[1]->getUsername());
        $this->assertEquals('herminia.hahn@gmail.com', $users[1]->getEmail());

        $this->assertEquals('a9c69793-81e3-31c5-b8ad-8d2282c7dfdb', $users[2]->getId());
        $this->assertEquals('admin', $users[2]->getUsername());
        $this->assertEquals('heloise.littel@kiehn.com', $users[2]->getEmail());
    }

    function test_makes_single_entity()
    {
        $user = new EntityBuilderUser();
        $this->entityFactory->shouldReceive('create')->once()->with(EntityBuilderUser::class)->andReturn($user);
        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(function (EntityBuilderUser $user, array $attributes) {
            return $this->hydrator->hydrate($attributes, $user);
        });

        $user = $this->builder->make();

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->getId());
        $this->assertEquals('zetta86', $user->getUsername());
        $this->assertEquals('tsteuber@hotmail.com', $user->getEmail());
    }

    function test_makes_single_entity_with_predefined_attributes()
    {
        $user = new EntityBuilderUser();
        $this->entityFactory->shouldReceive('create')->once()->with(EntityBuilderUser::class)->andReturn($user);
        $this->entityFactory->shouldReceive('hydrate')->once()->andReturnUsing(function (EntityBuilderUser $user, array $attributes) {
            return $this->hydrator->hydrate($attributes, $user);
        });

        $user = $this->builder->make(['email' => 'test@site.com']);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $user->getId());
        $this->assertEquals('zetta86', $user->getUsername());
        $this->assertEquals('test@site.com', $user->getEmail());
    }

    function test_makes_multiply_entity()
    {
        $this->entityFactory->shouldReceive('create')->times(3)->with(EntityBuilderUser::class)->andReturnUsing(function () {
            return new EntityBuilderUser();
        });
        $this->entityFactory->shouldReceive('hydrate')->times(3)->andReturnUsing(function (EntityBuilderUser $user, array $attributes) {
            return $this->hydrator->hydrate($attributes, $user);
        });

        $users = $this->builder->times(3)->make();

        $this->assertCount(3, $users);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $users[0]->getId());
        $this->assertEquals('zetta86', $users[0]->getUsername());
        $this->assertEquals('tsteuber@hotmail.com', $users[0]->getEmail());

        $this->assertEquals('3167f744-197b-3c9b-9419-71e5daf2ea18', $users[1]->getId());
        $this->assertEquals('erna39', $users[1]->getUsername());
        $this->assertEquals('herminia.hahn@gmail.com', $users[1]->getEmail());

        $this->assertEquals('a9c69793-81e3-31c5-b8ad-8d2282c7dfdb', $users[2]->getId());
        $this->assertEquals('nikki97', $users[2]->getUsername());
        $this->assertEquals('heloise.littel@kiehn.com', $users[2]->getEmail());
    }

    function test_makes_multiply_entity_with_predefined_attributes()
    {
        $this->entityFactory->shouldReceive('create')->times(3)->with(EntityBuilderUser::class)->andReturnUsing(function () {
            return new EntityBuilderUser();
        });

        $this->entityFactory->shouldReceive('hydrate')->times(3)->andReturnUsing(function (EntityBuilderUser $user, array $attributes) {
            return $this->hydrator->hydrate($attributes, $user);
        });

        $users = $this->builder->times(3)->make(['email' => 'test@site.com']);

        $this->assertCount(3, $users);

        $this->assertEquals('0b13e52d-b058-32fb-8507-10dec634a07c', $users[0]->getId());
        $this->assertEquals('zetta86', $users[0]->getUsername());
        $this->assertEquals('test@site.com', $users[0]->getEmail());

        $this->assertEquals('3167f744-197b-3c9b-9419-71e5daf2ea18', $users[1]->getId());
        $this->assertEquals('erna39', $users[1]->getUsername());
        $this->assertEquals('test@site.com', $users[1]->getEmail());

        $this->assertEquals('a9c69793-81e3-31c5-b8ad-8d2282c7dfdb', $users[2]->getId());
        $this->assertEquals('nikki97', $users[2]->getUsername());
        $this->assertEquals('test@site.com', $users[2]->getEmail());
    }

    function test_gets_raw_attributes_for_single_entity()
    {
        $this->assertEquals([
            'id' => '0b13e52d-b058-32fb-8507-10dec634a07c',
            'username' => 'zetta86',
            'email' => 'tsteuber@hotmail.com'
        ], $this->builder->raw());
    }

    function test_gets_raw_attributes_for_multiply_entities()
    {
        $this->assertEquals([
            [
                'id' => '0b13e52d-b058-32fb-8507-10dec634a07c',
                'username' => 'zetta86',
                'email' => 'tsteuber@hotmail.com'
            ],
            [
                'id' => '3167f744-197b-3c9b-9419-71e5daf2ea18',
                'username' => 'erna39',
                'email' => 'herminia.hahn@gmail.com'
            ],
            [
                'id' => 'a9c69793-81e3-31c5-b8ad-8d2282c7dfdb',
                'username' => 'nikki97',
                'email' => 'heloise.littel@kiehn.com'
            ]
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