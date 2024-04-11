<h3 align="center">Atom DI</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](/LICENSE)
[![Build Status](https://travis-ci.org/phpatom/DI.svg?branch=master)](https://travis-ci.org/phpatom/DI)
[![codecov](https://codecov.io/gh/phpatom/DI/branch/master/graph/badge.svg)](https://codecov.io/gh/phpatom/DI)


</div>

---

<p align="center">
    An expressive and flexible dependency injection container for PHP
    <br> 
</p>

## 📝 Table of Contents

- [Prerequisites](#prerequisites)
- [Installing](#installing)
- [Testing](#testing)
- [Coding Style](#coding_style)
- [Getting Started](#getting_started)
- [Usage](#usage)
- [Contributing](#contributing)
- [Authors](#authors)


## Prerequisites <a name = "prerequisites"></a>


- PHP 8.2 +
- Composer 


## Installing <a name = "installing"></a>

The recommended way to install is via Composer:


```
composer require phpatom/di
```


## Testing Installing <a name = "testing"></a>
 
```
composer test
```

### Coding style <a name = "coding_style"></a>

```
./vendor/bin/phpcs
```

## Getting Started <a name = "getting_started"></a>

### Instantiate the container

```php
use Atom\DI\Container;

$container = new Container();
```
### Basic usage

```php
use Atom\DI\Definition;
use Atom\DI\Definition;

require_once "vendor/autoload.php";

class User
{
    public function __construct(public readonly string $name)
    {
    }
}

class UserRepository
{
    public function __construct(private User $user)
    {

    }
    public function getUser(): User
    {
        return $this->user;
    }

}

class AuthService
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function getUser(): User
    {
        return $this->userRepository->getUser();
    }

}

class Controller
{

    public function index(AuthService $authService): void
    {
        echo sprintf("Hello %s\n", $authService->getUser()->name);
    }

}

use Atom\DI\Container;

$container = new Container();

$definition = $container->bind(AuthService::class)
    ->toNewInstance()
    ->withClass(
        UserRepository::class,
        Definition::newInstanceOf(UserRepository::class)
            ->withParameter('user', new User(name: "daniel"))
    );


// output: hello daniel
$container->callMethod(Controller::class, 'index');
```

### Factories

```php
use Atom\DI\Container;
use Atom\DI\Definition;

$container = new Container();

$definition = $container->bind(AuthService::class)
    ->toNewInstance()
    ->withClass(
        UserRepository::class,
        Definition::newInstanceOf(UserRepository::class)
            ->withParameter('user', new User(name: "daniel"))
    );

class UserFactory
{
    public function __construct(
        private int $previousId = 0
    ) {
    }

    public function makeUser(): User
    {
        $user = new User(
            sprintf("User #%d", $this->previousId),
            id: $this->previousId,
        );
        $this->previousId++;
        return $user;

    }
}

/**
 * the definition is either a prototype or a singleton, singleton is the default
 */
$container->bind(UserFactory::class)->singleton();
// output: hello daniel
$container->bind(
    User::class,
    Definition::callTo("makeUser")
        ->method()
        ->on(Definition::get(UserFactory::class))
)
    ->prototype(); // without this, the instance will be cached
// output: int(0)
var_dump($container->get(User::class)->id);
// output: int(1)
var_dump($container->get(User::class)->id);
```
### Values

```php
use Atom\DI\Container;
use Atom\DI\Definition;

$container = new Container();

$container->bind('foo', "bar");
//or
$container->bind('daniel')
    ->toValue(new User(name: "Nghokeng Daniel", id: 28));

//output: bar
echo $container->get('foo'), "\n";
```
### Array access

```php
use Atom\DI\Container;
use Atom\DI\Definition;

$container = new Container();
function makeDaniel()
{
    return new User("daniel", 28);
}

$container['foo'] = "bar";
$container['daniel'] = Definition::callTo("makeDaniel")
    ->function();

//output: bar
echo $container['foo'], "\n";
//output: daniel
echo $container['daniel']->name, "\n";
```


## Contributing <a name = "contributing"></a>
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.


## ✍️ Author <a name = "authors"></a>

- [@dani-gouken](https://github.com/dani-gouken) - Idea & Initial work

