<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use ReflectionException;

/**
 * @template T of object
 */
class BuildObject extends AbstractDefinition
{
    /**
     * @var class-string<T>
     */
    private $className;

    /**
     * BuildObject constructor.
     * @param class-string<T> $className
     * @param array<string, mixed> $parameters
     */
    public function __construct(string $className, array $parameters = [])
    {
        $this->className = $className;
        $this->parametersOverride = $parameters;
    }

    /**
     * @param Container $container
     * @return T|mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function interpret(Container $container): mixed
    {
        return $container->make($this->className, $this->parametersOverride, $this->classesOverride);
    }

    /**
     * @return class-string<T>
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
