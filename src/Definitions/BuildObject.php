<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use ReflectionException;

class BuildObject extends AbstractDefinition
{
    /**
     * @var string
     */
    private $className;

    /**
     * BuildObject constructor.
     * @param string $className
     * @param array $parameters
     */
    public function __construct(string $className, array $parameters = [])
    {
        $this->className = $className;
        $this->parametersOverride = $parameters;
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
        return $container->make($this->className, $this->parametersOverride, $this->classesOverride);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
