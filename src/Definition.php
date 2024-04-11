<?php


namespace Atom\DI;

use Atom\DI\Definitions\BuildObject;
use Atom\DI\Definitions\Get;
use Atom\DI\Definitions\Value;

class Definition
{
    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string,mixed> $constructorParameters
     */
    public static function newInstanceOf(string $className, array $constructorParameters = []): BuildObject
    {
        return new BuildObject($className, $constructorParameters);
    }

    public static function get(string $key): Get
    {
        return new Get($key);
    }

    public static function value(mixed $value): Value
    {
        return new Value($value);
    }

    public static function object(object $object): Value
    {
        return new Value($object);
    }

    /**
     * @param array<string,mixed> $parameters 
     */
    public static function callTo(callable|string $callable, array $parameters = []): CallableDefinitionFactory
    {
        return new CallableDefinitionFactory($callable, $parameters);
    }
}
