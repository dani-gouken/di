<?php


namespace Atom\DI;

use Atom\DI\Definitions\BuildObject;
use Atom\DI\Definitions\Get;
use Atom\DI\Definitions\Value;

class Definition
{
    public static function newInstanceOf(string $className, array $constructorParameter = []): BuildObject
    {
        return new BuildObject($className, $constructorParameter);
    }

    public static function get(string $key): Get
    {
        return new Get($key);
    }

    public static function value($value): Value
    {
        return new Value($value);
    }

    public static function object(object $object): Value
    {
        return new Value($object);
    }

    public static function callTo($callable, $parameters = []): CallableDefinitionFactory
    {
        return new CallableDefinitionFactory($callable, $parameters);
    }
}
