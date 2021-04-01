<?php

namespace Atom\DI;

use ArrayAccess;
use Atom\DI\Contracts\BindingContract;
use Atom\DI\Contracts\DefinitionContract;
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
     * @param bool $skipIfExists
     * @return Binding |BindingContract
     * @throws MultipleBindingException
     */
    public function bind($aliases, $definition = null, bool $skipIfExists = false): BindingContract
    {
        if (is_null($definition) && is_string($aliases)) {
            $definition = Definition::newInstanceOf($aliases);
        }
        if ((is_object($definition) &&
                !($definition instanceof DefinitionContract)) || is_scalar($definition)) {
            $definition = Definition::value($definition);
        }
        $aliases = is_string($aliases) ? [$aliases] : $aliases;
        $binding = new Binding($definition);
        foreach ($aliases as $alias) {
            $this->registerBinding($alias, $binding, $skipIfExists);
        }
        return $binding;
    }

    /**
     * @param $aliases
     * @param DefinitionContract|null|mixed $definition
     * @return BindingContract
     * @throws MultipleBindingException
     */
    public function bindIfNotAvailable($aliases, $definition = null): BindingContract
    {
        return $this->bind($aliases, $definition, true);
    }

    /**
     * @param $aliases
     * @return BindingContract|Binding
     * @throws MultipleBindingException
     */
    public function prototype($aliases): BindingContract
    {
        return $this->bind($aliases)->prototype();
    }

    /**
     * Return a value store inside the container
     * @param $id
     * @param array $args
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
     * @param array $args
     * @param bool $makeIfNotAvailable
     * @return mixed|void
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
            $result = $this->make($alias, is_array($args) ? $args : [$args], []);
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

    public function addResolvedValue(string $alias, $value)
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
     * @param $id
     * @return bool
     */
    public function remove($id): bool
    {
        if (!$this->has($id)) {
            return false;
        }
        unset($this->mapping[$id]);
        return true;
    }

    /**
     * @param string $className
     * @param array $params
     * @param array $classes
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function make(string $className, array $params = [], $classes = [])
    {
        try {
            $reflectedClass = new ReflectionClass($className);
        } catch (Exception $e) {
            throw new ContainerException("Unable to resolve the class [$className]");
        }
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
     * @param callable|string $function
     * @param array $parameters
     * @param array $classes
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function callFunction($function, array $parameters = [], array $classes = [])
    {
        $reflectedFunction = new ReflectionFunction($function);
        $closure = $reflectedFunction->getClosure();
        $params = $this->getFunctionParameters($reflectedFunction, $this, $parameters, $classes);
        return call_user_func_array($closure, $params);
    }

    /**
     * @param $object
     * @param string $method
     * @param array $parameters
     * @param array $classes
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function callMethod($object, string $method = "__invoke", array $parameters = [], array $classes = [])
    {
        if ($object instanceof DefinitionContract) {
            $object = $this->interpret($object);
        }
        if (is_string($object)) {
            $object = $this->make($object);
        }
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

    public function resolved($key, ?callable $callback = null)
    {
        if (($callback != null && !is_string($key)) || (is_null($callback) && !is_callable($key))) {
            throw new InvalidArgumentException("The resolution callback must be a valid callable");
        }
        if ($callback == null) {
            $this->globalResolutionCallback = $key;
        } else {
            $this->resolutionCallback[$key] = $callback;
        }
    }

    /**
     * @param string $alias
     * @return Binding
     */
    private function getBinding(string $alias): BindingContract
    {
        return $this->mapping[$alias];
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed|void
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws MultipleBindingException
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof DefinitionContract) {
            $this->bind($offset, $value);
        } else {
            $this->bind($offset)->toValue($value);
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * @param string $alias
     * @param BindingContract $binding
     * @param bool $skipIfExists
     * @throws MultipleBindingException
     */
    private function registerBinding(string $alias, BindingContract $binding, bool $skipIfExists = false)
    {
        if ($this->has($alias)) {
            if (!$skipIfExists) {
                throw new MultipleBindingException($alias);
            }
        } else {
            $this->mapping[$alias] = $binding;
        }
    }
}
