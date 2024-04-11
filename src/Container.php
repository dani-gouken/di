<?php

namespace Atom\DI;

use ArrayAccess;
use Atom\DI\Contracts\BindingContract;
use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Definitions\Value;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\MultipleBindingException;
use Atom\DI\Exceptions\NotFoundException;
use Exception;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;


class Container implements ContainerInterface, ArrayAccess
{
    use ParameterResolverTrait;

    /**
     * @var array<string,mixed>
     */
    private array $resolved = [];

    /**
     * @var array<string,BindingContract>
     */
    private array $mapping = [];

    /**
     * @var callable
     */
    private $globalResolutionCallback;
    /**
     * @var array<string,callable>
     */
    private array $resolutionCallback = [];

    /**
     * @var ResolutionStack
     */
    private ResolutionStack $resolutionStack;

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->resolutionStack = new ResolutionStack();
    }


    /**
     * @param array<string>|string $aliases
     * @param DefinitionContract|null|mixed $definition
     * @return Binding |BindingContract
     * @throws MultipleBindingException
     */
    public function bind($aliases, $definition = null, string $scope = BindingContract::SCOPE_SINGLETON): BindingContract
    {
        if (is_null($definition) && is_string($aliases)) {
            /** @var class-string $aliases **/
            $definition = Definition::newInstanceOf($aliases);
        }
        if (
            (is_object($definition) &&
                !($definition instanceof DefinitionContract)) || is_scalar($definition)
        ) {
            $definition = Definition::value($definition);
        }
        $aliases = is_string($aliases) ? [$aliases] : $aliases;
        /** @var DefinitionContract|null $definition **/
        $binding = new Binding($definition, scope: $scope);
        foreach ($aliases as $alias) {
            $this->registerBinding($alias, $binding);
        }
        return $binding;
    }

    /**
     * @param array<string> $aliases
     * @param DefinitionContract|null $definition
     * @return BindingContract
     */
    public function bindIfNotAvailable($aliases, ?DefinitionContract $definition = null): BindingContract
    {
        try {
            return $this->bind($aliases, $definition);
        } catch (MultipleBindingException $exception) {
            return $this->getBinding($exception->alias);
        }
    }

    /**
     * @param array<string>|string $aliases
     * @return BindingContract|Binding
     * @throws MultipleBindingException
     */
    public function prototype($aliases): BindingContract
    {
        return $this->bind($aliases, scope: BindingContract::SCOPE_PROTOTYPE);
    }

    /**
     * Return a value store inside the container
     * @param $id
     * @param array<string,mixed> $args
     * @param bool $makeIfNotAvailable
     * @return mixed|void
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function get($id, $args = [], $makeIfNotAvailable = true)
    {
        $this->resolutionStack->clear();
        $result = $this->getDependency($id, $args, $makeIfNotAvailable);
        $this->resolutionStack->clear();
        return $result;
    }

    /**
     * Return a value store inside de container
     * @param string $alias
     * @param array<string,mixed> $args
     * @param bool $makeIfNotAvailable
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function getDependency(string $alias, $args = [], bool $makeIfNotAvailable = true)
    {
        $this->resolutionStack->append($alias);
        if (array_key_exists($alias, $this->resolved)) {
            return $this->resolved[$alias];
        }
        $result = null;
        if ($this->has($alias)) {
            $result = $this->getBinding($alias)->getValue($alias, $this);
        } elseif ($makeIfNotAvailable) {
            /** @var class-string $alias **/
            $result = $this->make($alias, $args, []);
        } else {
            throw new NotFoundException($alias);
        }
        if (array_key_exists($alias, $this->resolutionCallback)) {
            $result = $this->resolutionCallback[$alias]($result, $this) ?? $result;
        }
        if ($this->globalResolutionCallback != null) {
            $c = $this->globalResolutionCallback;
            $result = $c($alias, $result, $this);
        }
        return $result;
    }

    public function addResolvedValue(string $alias, mixed $value): void
    {
        $this->resolved[$alias] = $value;
    }

    /**
     * @param DefinitionContract $definition
     * @return mixed
     */
    public function interpret(DefinitionContract $definition)
    {
        $result = $definition->interpret($this);
        if ($definition->getResolutionCallback() != null) {
            $result = $definition->getResolutionCallback()($result, $this) ?? $result;
        }
        return $result;
    }

    /**
     * check if the container can build the object that has the given alias
     * @param $id
     * @return bool
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->mapping);
    }

    /**
     * check if the container can build the object that has the given alias
     * @param string $id
     * @return bool
     */
    public function remove(string $id): bool
    {
        if (!$this->has($id)) {
            return false;
        }
        unset($this->mapping[$id]);
        return true;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string,mixed> $params
     * @param array<string,mixed> $classes
     * @return object|T
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function make(string $className, array $params = [], $classes = [])
    {
        if (!class_exists($className)) {
            throw new ContainerException("The class [$className] does not exists");
        }

        $reflectedClass = new ReflectionClass($className);
        if (!$reflectedClass->isInstantiable()) {
            throw new ContainerException("The class [$className] is not instantiable");
        }
        $constructor = $reflectedClass->getConstructor();
        if (is_null($constructor)) {
            return new $className;
        }
        $resolvedParameters = $this->getFunctionParameters(
            $constructor,
            $this,
            $params,
            $classes
        );
        return $reflectedClass->newInstanceArgs($resolvedParameters);
    }

    /**
     * @param \Closure|string $function
     * @param array<string,mixed> $parameters
     * @param array<string,mixed> $classes
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function callFunction($function, array $parameters = [], array $classes = []): mixed
    {
        $reflectedFunction = new ReflectionFunction($function);
        $closure = $reflectedFunction->getClosure();
        if ($closure == null) {
            throw new ContainerException("failed to load closure");
        }
        $params = $this->getFunctionParameters($reflectedFunction, $this, $parameters, $classes);
        return call_user_func_array($closure, $params);
    }

    /**
     * @template T
     * @param DefinitionContract|class-string<T>|object $object
     * @param string $method
     * @param array<string,mixed> $parameters
     * @param array<string,mixed> $classes
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function callMethod(string|object $object, string $method = "__invoke", array $parameters = [], array $classes = [])
    {
        if ($object instanceof DefinitionContract) {
            $object = $this->interpret($object);
        }
        /** @var class-string $object */
        if (is_string($object)) {
            $object = $this->make($object);
        }
        /** @var object $object */
        $reflectedMethod = new ReflectionMethod($object, $method);
        $methodParams = $this->getFunctionParameters($reflectedMethod, $this, $parameters, $classes);
        return $reflectedMethod->invokeArgs($object, $methodParams);
    }

    /**
     * @return ResolutionStack
     */
    public function getResolutionStack(): ResolutionStack
    {
        return $this->resolutionStack;
    }

    public function resolved(string|callable $key, ?callable $callback = null): void
    {
        if (($callback != null && !is_string($key)) || (is_null($callback) && !is_callable($key))) {
            throw new InvalidArgumentException("The resolution callback must be a valid callable");
        }
        if ($callback == null && is_callable($key)) {
            $this->globalResolutionCallback = $key;
        } else if (is_string($key) && !is_null($callback)) {
            $this->resolutionCallback[$key] = $callback;
        }
    }

    /**
     * @param string $alias
     * @return BindingContract|Binding
     */
    private function getBinding(string $alias): BindingContract
    {
        return $this->mapping[$alias];
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     * @return mixed|void
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     * @throws MultipleBindingException
     */
    public function offsetSet($offset, $value): void
    {
        if ($value instanceof DefinitionContract) {
            $this->bind($offset, $value);
        } else {
            $this->bind($offset, new Value($value));
        }
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * @param string $alias
     * @param BindingContract $binding
     * @throws MultipleBindingException
     */
    private function registerBinding(string $alias, BindingContract $binding): void
    {
        if ($this->has($alias)) {
            throw new MultipleBindingException($alias);
        }
        $this->mapping[$alias] = $binding;
    }
}
