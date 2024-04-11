<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;
use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use ReflectionException;

class CallMethod extends AbstractDefinition
{
    /**
     * @var DefinitionContract|object|class-string $object
     */
    private object|string $object;

    /**
     * @param array<string,mixed> $parametersOverride
     */
    public function __construct(
        private string $methodName = "__invoke",
        array $parametersOverride = []
    ) {
        $this->parametersOverride = $parametersOverride;
    }

    /**
     * @param DefinitionContract|object|class-string $object
     * @return CallMethod
     */
    public function on(object|string $object): self
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return object|class-string|DefinitionContract
     */
    public function getObject(): object|string
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
    public function interpret(Container $container): mixed
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
