<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use ReflectionException;

class CallMethod extends AbstractDefinition
{
    /**
     * @var object
     */
    private $object;
    /**
     * @var string
     */
    private $methodName;

    public function __construct(string $methodName = "__invoke", array $parameters = [])
    {
        $this->methodName = $methodName;
        $this->parametersOverride = $parameters;
    }

    /**
     * @param $object
     * @return CallMethod
     */
    public function on($object): self
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return object|string
     */
    public function getObject()
    {
        return $this->object;
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
        return $container->callMethod(
            $this->getObject(),
            $this->methodName,
            $this->parametersOverride,
            $this->classesOverride
        );
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->methodName;
    }
}
