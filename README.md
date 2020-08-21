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


- PHP 7.3 +
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
```php
//instantiate the container
$dic = new Atom\DI\DIC();
//it implement psr container inteface
var_dump($dic instanceof psr\container\ContainerInterface); //true    

//store bindings in the container
$dic->factories()->store(
    MyServiceInterface::class, 
    $dic->as()->instanceOf(MyService::class)
    ->withParameter("servicekey",$dic->as()->get("servicekey"))
    ->resolved(function($key){
        return "mysuper"  + $key;
    })
);
$dic->bindings()->store("serviceKey",$dic->as()->value("myservicekey"));

//build your object
$service = $dic->get(MyServiceInterface::class);
var_dump($service instanceof MyServiceInterface); //true
echo $service->getServiceKey(); //mysuperservicekey
```

## Usage <a name="usage"></a>

This library is based 02 concepts: ``Storage`` and `` Definitions``
- A definition is a classed that explain how a value should be resolved 
- A Storage is a class that is used to store definitions and manage how they are resolved. 
A storage can only store definitions.

### The value storage
it can be accessed through the ``bindings`` method in the container
```php
$dic = new Atom\DI\DIC();
var_dump($dic->bindings() instanceof \Atom\DI\Storage\ValueStorage); //true
``` 
The values storage can take any type of definitions. Those definitions will be executed each time the container will try to access the definitions.
It means that if the definition is responsible of instantiating an object, the container will generate a different instance each time.
This storage is suitable to store plain values or instance
-  bind values
```php
   $dic = new \Atom\DI\DIC();
   $dic->bindings()->store("foo",$dic->as()->value("bar"));
   echo $dic->get("foo"); // bar;
   $dic->bindings()->store("baz",$dic->as()->value(function(){
    echo "i am baz";
    }));
   $dic->get("baz")(); // i am baz 
``` 
The method `` $dic->as()`` return a definition factory. It can be used to generate definitions.
for example `` $dic->as()->value() `` will return an object of type ``Atom\Definitions\Value``;
-  bind instances
```php
    class Service{
    }   
   $dic = new \Atom\DI\DIC();
   $dic->bindings()->store(Service::class,$dic->as()->object(new Service()));
   echo $dic->get(Service::class) instanceof Service ; // true
```
or
```php   
   $dic = new \Atom\DI\DIC();
   $dic->bindings()->bindInstance(new Service());
   echo $dic->get(Service::class) instanceof Service ; // true
```
``$dic->as()->object()`` is an alias for ``$dic->as()->value() ``, since we are still storing values.

- bind classes
```php
class Engine{
    private $model;
    public function __construct($model) {$this->model = $model;}
    /**
     * @return mixed
     */public  function getModel(){
        return $this->model;
    }
}

class Car{
    /**
    * @var Engine
    */
    private $engine;
    public $model;
    public function __construct(Engine $engine,$model) {
        $this->engine = $engine;
        $this->model = $model;
    }
    /**
    * @return Engine
    */
    public  function getEngine(): Engine{
        return $this->engine;
    }
}
$dic = new \Atom\DI\DIC();
$dic->bindings()->store("engine_model",$dic->as()->value("XYZ"));
$dic->store(
    Car::class,
    $dic->as()->instanceOf(Car::class)
    ->withConstructorParameters(["model"=>"lamborghini veneno"])
    ->with(
        Engine::class,
        $dic->as()->instanceOf(Engine::class)
            ->withParameter("model",$dic->as()->get("engine_model"))
    )
);


$mycar = $dic->get(Car::class);
var_dump($mycar instanceof Car); //true
echo $mycar->getEngine()->getModel(); // XYZ
echo $mycar->model; // lamborghini veneno
```
The method ``$dic->as()->instanceOf(Car::class)`` will generate a new instance of ``Atom\DI\Definition\BuildObject``. It is a definition used to explain how an object should be build.
<br>- ``withConstructorParameter`` is used set the values that will be passed to the constructor of the object.
<br>- ``with`` is used to bind a class name to the constructor. it basically says that, if the constructor needs an instance of ``Engine``, use this definitions. That definition can be any valid definitions, from a fixed value, a new instance to method call.
Here, the call to ``$dic->as()->get("engine_model")`` will return an instance of ``Oxygen\Definition\Get``. It is used to resolve a value from the container 
<br>- ``withParameter`` can be used to bind a parameter name to a definition.
```php
$dic = new \Atom\DI\DIC();
$dic->bindings()->store("car_model","lamborghini veneno");
$dic->bindings()->store(
    Car::class,
    $dic->as()->instanceOf(Car::class)
    ->withParameter("model",$dic->as()->get("car_model"))
);
```
### Singleton storage
It works exactly like the Value Storage, with the only difference that it will return the same instance every time. This storage will execute your definition once and cache the result. That result will be return every time that the container want to access your value;
it can be accessed through the ``singletons`` method.
```php
$dic = new \Atom\DI\DIC();
echo $dic->singletons() instanceof \Atom\DI\Storage\SingletonStorage; // true
$dic->singletons()->store(
    Car::class,
    $dic->as()->instanceOf(Car::class)
    ->withParameter("model",$dic->as()->get("car_model"))
);
$dic->get(Car::class); // instance
$dic->get(Car::class); // same instance
$dic->get(Car::class); // same instance
```   

### The factory Storage
It can be used to store factories for specific object. The factory can be either a function or a method call.
Those method will be called using dependency injection. It means that the parameters can be injected directly to the function
- Method call
```php
    class Car{
        private  $name;
        public function __construct() {}
        /**
         * @param mixed $name
         */
        public  function setName($name):void {
            $this->name = $name;
         }
        /**
         * @return mixed
         */public  function getName(){
            return $this->name;
        }
    }   
    class MyCarFactory{
        private $manufacturer;
        public function __construct($manufacturer = "") {$this->manufacturer = $manufacturer;}
        public function __invoke(string $name){
           $car =  new Car();
            $car->setName($name);
        }
    }
    $dic = new \Atom\DI\DIC();
    $dic->factories()->store(
        Car::class,
        $dic->as()->callTo("__invoke")->method()
            ->on(MyCarFactory::class)
            ->withParameter("name",$dic->as()->value("A car"))
    );
    echo $dic->get(Car::class) instanceof Car; // true  
```
The value passed to ``on()`` can be either a namespace, an instance, or another definition  
```php
    $dic = new \Atom\DI\DIC();
    $dic->bindings()->store("manufacturer",$dic->as()->value("lamborghini"));
    $dic->factories()->store(
        Car::class,
        $dic->as()->callTo("__invoke")->method()
            ->on(
                $dic->new()->instanceOf(MyCarFactory::class)
                ->withParameter("manufacturer",$dic->as()->get("manufacturer"))
            )->withParameter("name",$dic->as()->value("A car"))
    );  
```
The method ``new()`` is an alias for ``as()``
- Function call
```php
    class User{
        private $name;
        private $id;
        public function __construct() {
            $this->id = rand();
        }
        /**
         * @return mixed
         */
        public  function getName(){
            return $this->name;
        }
        /**
         * @param mixed $name
         */
         public  function setName($name):void {
            $this->name = $name;
         }
    }   
    class UserFactory{
        private $name;
        public function __construct($name) {$this->name = $name;}
        public function make(){
            $user = new User();
            $user->setName($this->name);
            return $user;
        }
    }
    function generateUser(UserFactory $factory){
        return $factory->make();
    }
    $dic = new \Atom\DI\DIC();
    $dic->factories()->store(
        User::class,
        $dic->as()->callTo("generateUser")
            ->function()
            ->with(
                UserFactory::class,
                $dic->as()->instanceOf(UserFactory::class)
                ->withConstructorParameters(["name" => "admin"])
            )           
    );
    $user = $dic->get(User::class);
    echo $user->getName(); //admin 
```
### Wildcard Storage
it's used to map a namespace that matches a specific pattern to another one
```php
$dic = new \Atom\DI\DIC();
echo $dic->wildcards() instanceof \Atom\DI\Storage\WildcardStorage; // true
```
- Store a wildcard
```php

$dic = new \Atom\DI\DIC();
$dic->wildcards()->store(
    'Blog\Domain\*RepositoryInterface',
    $dic->as()->wildcardFor('Blog\Architecture\*DoctrineRepository')
);
$dic->get(Blog\Domain\UserRepositoryInterface::class); // will return an instance of Blog\Architecture\UserDoctrineRepository
```
## Contributing <a name = "contributing"></a>
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.


## ✍️ Author <a name = "authors"></a>

- [@dani-gouken](https://github.com/dani-gouken) - Idea & Initial work

