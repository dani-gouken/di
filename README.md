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

- [About](#about)
- [Getting Started](#getting_started)
- [Deployment](#deployment)
- [Usage](#usage)
- [Built Using](#built_using)
- [TODO](../TODO.md)
- [Contributing](../CONTRIBUTING.md)
- [Authors](#authors)
- [Acknowledgments](#acknowledgement)


### Prerequisites


- PHP 7.3 +
- Composer 


### Installing

The recommended way to install is via Composer:


```
composer require phpatom/di
```


##Testing
 
```
composer test
```

### Coding style

```
./vendor/bin/phpcs
```

## Getting Started <a name = "getting_started"></a>
```php
//instantiate the container
$dic = new Atom\DI\DIC();
//it implement psr container inteface
var_dump($dic instance of psr\container\ContainerInterface); //true    

//store values in the container
$dic->factories()->store(
    MyServiceInterface::class, 
    $dic->as()->instanceOf(MyService::class)
    ->withParameter("servicekey",$dic->as()->get("servicekey"))
    ->resolved(function($key){
        return "mysuper"  + $key;
    })
);
$dic->values()->store("serviceKey",$dic->as()->value("myservicekey"));

//build your object
$service = $dic->get(MyServiceInterface::class);
var_dump($service instanceof MyServiceInterface)); //true
echo $service->getServiceKey() //mysuperservicekey
```

## Usage <a name="usage"></a>

...

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.


## ✍️ Author <a name = "authors"></a>

- [@dani-gouken](https://github.com/dani-gouken) - Idea & Initial work

