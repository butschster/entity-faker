<?php
declare(strict_types=1);

namespace Butschster\Tests;

use Butschster\EntityFaker\EntityFactory\InstanceWithoutConstructorStrategy;
use Butschster\EntityFaker\LaminasEntityFactory;
use Laminas\Hydrator\ReflectionHydrator;

class LaminasEntityFactoryTest extends TestCase
{
    private LaminasEntityFactory $entityFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityFactory = new LaminasEntityFactory(
            new ReflectionHydrator(),
            new InstanceWithoutConstructorStrategy()
        );
    }

    function test_creates_entity_instance_by_class_name()
    {
        $user = $this->entityFactory->create(LaminasEntityFactoryUser::class);

        $this->assertInstanceOf(LaminasEntityFactoryUser::class, $user);
        $this->assertUninitializedProperty($user, 'id');
        $this->assertUninitializedProperty($user, 'username');
        $this->assertUninitializedProperty($user, 'email');
    }

    function test_hydrate_entity()
    {
        $user = $this->entityFactory->create(LaminasEntityFactoryUser::class);

        $user = $this->entityFactory->hydrate($user, [
            'id' => 123,
            'username' => 'guest',
            'email' => 'test@site.com'
        ]);

        $this->assertEquals(123, $user->getId());
        $this->assertEquals('guest', $user->getUsername());
        $this->assertEquals('test@site.com', $user->getEmail());
    }
}

class LaminasEntityFactoryUser
{
    private int $id;
    private string $username;
    private string $email;

    public function __construct(int $id, string $username, string $email)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
    }

    public function getId(): int
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
