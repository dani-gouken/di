<?php
require_once "vendor/autoload.php";

use Atom\DI\Container;
use Atom\DI\Definition;

function makeBar()
{
    return "bar";
}

class damn
{
    /**
     * @var string
     */
    private $yolo;

    public function __construct(string $yolo)
    {
        $this->yolo = $yolo;
    }

    /**
     * @return string
     */
    public function getYolo(): string
    {
        return $this->yolo;
    }
}

class myclass
{
    /**
     * @var string
     */
    private $foo;
    /**
     * @var damn
     */
    private $damn;

    public function __construct(string $foo, damn $damn)
    {
        $this->foo = $foo;
        $this->damn = $damn;
    }

    /**
     * @return string
     */
    public function getFoo(): string
    {
        echo "getFooooo\n";
        return $this->foo . "--" . $this->getDamn()->getYolo();
    }

    /**
     * @return damn
     */
    public function getDamn(): damn
    {
        return $this->damn;
    }
}

$container = new Container();
$container->bind("key")->toValue("secret");
$container->bind(["foo","bar"])
    ->prototype()
    ->toMethod("getFoo")->on(
        Definition::newInstanceOf("myclass")
            ->withParameter("foo", Definition::get("key"))
            ->withClass("damn", Definition::newInstanceOf("damn")
                ->withParameter("yolo", "bro"))
            ->resolved(function () {
                echo "class resolved";
            })
    );
var_dump($container["foo"]);
var_dump($container["bar"]);

