# Fake entities generator

This package will help you generate fake entities and persist them to your ORM.

```php
<?php

use Cycle\ORM\ORMInterface;
use Cycle\ORM\TransactionInterface;
use Faker\Factory as Faker;

class CycleOrmEntityFactory implements \Butschster\EntityFaker\EntityFactoryInterface {

    private ORMInterface $orm;
    private TransactionInterface $transaction;
    
    public function __construct(ORMInterface $orm, TransactionInterface $transaction) 
    {
        $this->orm = $orm;
        $this->transaction = $transaction;
    }
    
    public function create(string $class): object
    {
        $mapper = $this->orm->getMapper($class);
        
        return $mapper->init([]);
    }
    
    public function store(object $entity): void
    {
        $this->transaction->persist($entity);
    }
    
    public function hydrate(object $entity, array $data) : object
    {
        $mapper = $this->orm->getMapper($entity);
        
        return $mapper->hydrate($entity, $data);
    }
}

$factory = new \Butschster\EntityFaker\Factory(
    new CycleOrmEntityFactory(...),
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

$factory->define(User::class, function (Faker $faker, array $attributes) {
    return [
        'id' => $faker->uuid,
        'username' => $faker->username,
        'email' => $faker->email
    ];
});

$factory->define(SuperUser::class, function (Faker $faker, array $attributes) use($factory) {
    $userAttributes = $factory->raw(User::class);
    
    return $userAttributes + [
        'isAdmin' => $faker->boolean
    ];
});
```


### Create and persist an entity
```php
$user = $factory->of(User::class)->create();

class User {
  private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
  private string $username = "zetta86";
  private string $email = "tsteuber@hotmail.com";
}
```

### Create and persist multiply entities
```php
$users = $factory->of(User::class)->times(10)->create();

[
    class User {
      private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
      private string $username = "zetta86";
      private string $email = "tsteuber@hotmail.com";
    },
    ...
]
```

### Create and persist an entity with predefined attributes
```php
$user = $factory->of(User::class)->create([
    'email' => 'admin@site.com'
]);

class User {
  private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
  private string $username = "zetta86";
  private string $email = "admin@site.com";
}
```

### Create an entity
```php
$user = $factory->of(User::class)->make();

class User {
  private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
  private string $username = "zetta86";
  private string $email = "tsteuber@hotmail.com";
}
```

### Create multiply entities
```php
$users = $factory->of(User::class)->times(10)->make();

[
    class User {
      private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
      private string $username = "zetta86";
      private string $email = "tsteuber@hotmail.com";
    },
    ...
]
```

### Create an entity with predefined attributes
```php
$user = $factory->of(User::class)->make([
    'email' => 'admin@site.com'
]);

class User {
  private string $id = "0b13e52d-b058-32fb-8507-10dec634a07c";
  private string $username = "zetta86";
  private string $email = "admin@site.com";
}
```

### Get raw attributes for entity
```php
$attributes = $factory->of(SuperUser::class)->raw();

[
    'id' => "0b13e52d-b058-32fb-8507-10dec634a07c",
    'username' => 'zetta86',
    'email' => 'tsteuber@hotmail.com',
]
```

### Get raw attributes for entity with predefined values
```php
$attributes = $factory->of(SuperUser::class)->raw([
    'email' => 'test@site.com'
]);

[
    'id' => "0b13e52d-b058-32fb-8507-10dec634a07c",
    'username' => 'zetta86',
    'email' => 'test@site.com',
]
```
