<?php


namespace Atom\DI;

use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

trait ParameterResolverTrait
{
    /**
     * @var array<string, mixed>
     */
    protected $resolvedClassParameters = [];

    /**
     * @param ReflectionFunctionAbstract $method
     * @param ReflectionParameter $parameter
     * @param Container $container
     * @param array<string,mixed> $parameters
     * @param array<string,mixed> $classes
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function searchParameterValue(
        ReflectionFunctionAbstract $method,
        ReflectionParameter $parameter,
        Container $container,
        array $parameters = [],
        array $classes = []
    ): mixed {
        $paramName = $parameter->name;
        if (array_key_exists($paramName, $parameters)) {
            return $this->getOverrideValue($container, $parameters[$paramName]);
        }
        if ($parameter->isDefaultValueAvailable() || $parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }
        $paramClass = $this->getParameterClassName($parameter);
        if (is_null($paramClass)) {
            if ($method instanceof ReflectionMethod) {
                throw new ContainerException("Cannot resolve argument [{$parameter->name}] when trying to call 
                the method [$method->name] on [$method->class]");
            }
            throw new ContainerException("Cannot resolve argument [{$parameter->name}] 
                when trying to call the method [$method->name]");
        }
        if (array_key_exists($paramClass, $classes)) {
            return $this->getOverrideValue($container, $classes[$paramClass]);
        }
        if (array_key_exists($paramClass, $this->resolvedClassParameters)) {
            return $this->resolvedClassParameters[$paramClass];
        }
        $result = $container->getDependency($paramClass);
        $this->resolvedClassParameters[$paramClass] = $result;
        return $result;
    }

    /**
     * @param Container $container
     * @param DefinitionContract|mixed $override
     * @return mixed
     */
    private function getOverrideValue(Container $container, $override): mixed
    {
        if ($override instanceof DefinitionContract) {
            return $container->interpret($override);
        }
        return $override;
    }

    /**
     * return ReflectedFunction parameters as array
     *
     * @param ReflectionFunctionAbstract $method
     * @param Container $container
     * @param array<string,mixed> $parameters
     * @param array<string,mixed> $classes
     * @return array<string,mixed>
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    private function getFunctionParameters(
        ReflectionFunctionAbstract $method,
        Container $container,
        $parameters = [],
        $classes = []
    ): array {
        $result = [];
        foreach ($method->getParameters() as $index => $parameter) {
            $paramName = $parameter->getName();
            $result[$paramName] = $this->searchParameterValue(
                $method,
                $parameter,
                $container,
                $parameters,
                $classes
            );
        }
        return $result;
    }

    /**
     * return ReflectionParameter ClassName
     *
     * @param ReflectionParameter $param
     * @return string|null
     */
    private function getParameterClassName(ReflectionParameter $param): ?string
    {
        $type = $param->getType();
        if (is_null($type)) {
            return null;
        }
        if (!$type instanceof ReflectionNamedType) {
            return null;
        }
        if ($type->isBuiltin()) {
            return null;
        }
        return $type->getName();
    }
}
