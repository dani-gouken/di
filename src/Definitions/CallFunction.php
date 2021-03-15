<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use ReflectionException;

class CallFunction extends AbstractDefinition
{
    private $callable;

    public function __construct($callable, array $parameters = [])
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
    public function interpret(Container $container)
    {
        return $container->callFunction($this->callable, $this->parametersOverride, $this->classesOverride);
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->callable;
    }
}
