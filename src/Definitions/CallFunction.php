<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use ReflectionException;

class CallFunction extends AbstractDefinition
{
    /**
     * @param array<string,mixed> $parameters
     */
    public function __construct(private \Closure|string $callable, array $parameters = [])
    {
        $this->parametersOverride = $parameters;
        $this->callable = $callable;
    }

    /**
     * @param Container $container
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function interpret(Container $container): mixed
    {
        return $container->callFunction($this->callable, $this->parametersOverride, $this->classesOverride);
    }

    public function getCallable(): \Closure|string
    {
        return $this->callable;
    }
}
