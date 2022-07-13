# Fake entities generator

[![Support me on Patreon](https://img.shields.io/endpoint.svg?url=https%3A%2F%2Fshieldsio-patreon.vercel.app%2Fapi%3Fusername%3Dbutschster%26type%3Dpatrons&style=flat)](https://patreon.com/butschster)
[![Latest Stable Version](https://poser.pugx.org/butschster/entity-faker/v/stable)](https://packagist.org/packages/butschster/entity-faker)
[![Build Status](https://github.com/butschster/entity-faker/actions/workflows/php.yml/badge.svg)](https://github.com/butschster/entity-faker/actions/workflows/php.yml)
[![Total Downloads](https://poser.pugx.org/butschster/entity-faker/downloads)](https://packagist.org/packages/butschster/entity-faker)
[![License](https://poser.pugx.org/butschster/entity-faker/license)](https://packagist.org/packages/butschster/entity-faker)

This package will help you generate fake entities and persist them to your ORM.

```php
<?php

use Butschster\EntityFaker\LaminasEntityFactory;
use Butschster\EntityFaker\EntityFactory\InstanceWithoutConstructorStrategy;
use Laminas\Hydrator\ReflectionHydrator;
use Faker\Factory as Faker;

$factory = new \Butschster\EntityFaker\Factory(
    new LaminasEntityFactory(
        new ReflectionHydrator(),
        new InstanceWithoutConstructorStrategy()
    ),
    Faker::create()
);

class User 
{
    private string $id;
    private string $username;
    private string $email;
    
    public function __construct(string $id, string $username, string $email) 
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
    }
}

class SuperUser extends User
{
    private bool $isAdmin = false;
    
    public function __construct(string $id, string $username, string $email, bool $isAdmin) 
    {
        parent::__construct($id, $username, $email);
        $this->isAdmin = $isAdmin;
    }
}

$factory->define(User::class, static fn (Faker $faker, array $attributes) => [
        'id' => $faker->uuid,
        'username' => $faker->username,
        'email' => $faker->email
    ]);

$factory->define(SuperUser::class, static function (Faker $faker, array $attributes) use($factory) {
    $userAttributes = $factory->of(User::class)->raw();
    
    return $userAttributes + [
        'isAdmin' => $faker->boolean
    ];
});
```

### Create and persist an entity

```php
$user = $factory->of(User::class)->create();

//class User {
//  private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
//  private string $username = "zetta86";
//  private string $email = "tsteuber@hotmail.com";
//}
```

### Create and persist multiply entities

```php
$users = $factory->of(User::class)->times(10)->create();

//[
//    class User {
//      private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
//      private string $username = "zetta86";
//      private string $email = "tsteuber@hotmail.com";
//    },
//    ...
//]
```

### Create and persist an entity with predefined attributes

```php
$user = $factory->of(User::class)->create([
    'email' => 'admin@site.com'
]);

//class User {
//  private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
//  private string $username = "zetta86";
//  private string $email = "admin@site.com";
//}
```

### Create an entity

```php
$user = $factory->of(User::class)->make();

//class User {
//  private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
//  private string $username = "zetta86";
//  private string $email = "tsteuber@hotmail.com";
//}
```

### Create multiply entities

```php
$users = $factory->of(User::class)->times(10)->make();

//[
//    class User {
//      private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
//      private string $username = "zetta86";
//      private string $email = "tsteuber@hotmail.com";
//    },
//    ...
//]
```

### Create an entity with predefined attributes

```php
$user = $factory->of(User::class)->make([
    'email' => 'admin@site.com'
]);

//class User {
//  private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
//  private string $username = "zetta86";
//  private string $email = "admin@site.com";
//}
```

### Get raw attributes for entity

```php
$attributes = $factory->of(SuperUser::class)->raw();

//[
//    'id' => "0b13e52d-b058-32fb-8507-10dec634a07c",
//    'username' => 'zetta86',
//    'email' => 'tsteuber@hotmail.com',
//]
```

### Get raw attributes for entity with predefined values

```php
$attributes = $factory->of(SuperUser::class)->raw([
    'email' => 'test@site.com'
]);

//[
//    'id' => "0b13e52d-b058-32fb-8507-10dec634a07c",
//    'username' => 'zetta86',
//    'email' => 'test@site.com',
//]
```

### Generate array of all defined entities

```php
$repository = $factory->make(1000);

$seeds = $repository->get(User::class)->random(100);

$seeds = $repository->get(SuperUser::class)->take(50);
```

### Generate array of raw data for all defined entities

```php
$repository = $factory->raw(1000);

$seeds = $repository->get(User::class)->random(100);

$seeds = $repository->get(SuperUser::class)->take(50);
```

#### Custom entity builder

You can define your own EntityBuilder class with custom persist logic.

```php

use Butschster\EntityFaker\EntityFactoryInterface;
use Faker\Factory as Faker;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\TransactionInterface;

class CycleOrmEntityFactory implements EntityFactoryInterface 
{
    private array $afterCreation = [];
    private array $beforeCreation = [];

    protected ORMInterface $orm;
    protected Transaction $transaction;

    public function __construct(ORMInterface $orm)
    {
        $this->orm = $orm;

        $this->beforeCreation(function () {
            $this->transaction = new Transaction($this->orm);
        });

        $this->afterCreation(function () {
            $this->transaction->run();
        });
    }

    public function store(object $entity): void
    {
        $this->transaction->persist($entity);
    }

    public function hydrate(object $entity, array $data): object
    {
        return $this->orm->getMapper($entity)->hydrate($entity, $data);
    }

    /**
     * Add a callback to run after creating an entity or array of entities.
     * @param callable $callback
     */
    public function afterCreation(callable $callback): void
    {
        $this->afterCreation[] = $callback;
    }

    public function afterCreationCallbacks(): array
    {
        return $this->afterCreation;
    }

    /**
     * Add a callback to run before creating an entity or array of entities.
     * @param callable $callback
     */
    public function beforeCreation(callable $callback): void
    {
        $this->beforeCreation[] = $callback;
    }

    public function beforeCreationCallbacks(): array
    {
        return $this->beforeCreation;
    }
}

$factory = new \Butschster\EntityFaker\Factory(
    new CycleOrmEntityFactory(...),
    Faker::create()
);
```
