<?php


namespace Atom\DI\Definitions;

use Atom\DI\Contracts\DefinitionContract;

abstract class AbstractDefinition implements DefinitionContract
{
    /**
     * @var array<string,mixed>
     */
    protected $classesOverride = [];

    /**
     * @var array<string,mixed>
     */
    protected $parametersOverride = [];

    /**
     * @var ?callable
     */
    protected $resolutionCallback;

    /**
     * @param string $className
     * @return mixed|null
     */
    public function getClass(string $className): mixed
    {
        return $this->classesOverride[$className] ?? null;
    }

    /**
     * @param string $parameterName
     * @return mixed|null
     */
    public function getParameter(string $parameterName): mixed
    {
        return $this->parametersOverride[$parameterName] ?? null;
    }

    /**
     * @param string $parameterName
     * @param $value
     * @return $this
     */
    public function withParameter(string $parameterName, mixed $value): self
    {
        $this->parametersOverride[$parameterName] = $value;
        return $this;
    }

    /**
     * @param string $className
     * @param $value
     * @return $this
     */
    public function withClass(string $className, mixed $value): self
    {
        $this->classesOverride[$className] = $value;
        return $this;
    }

    /**
     * @param array<string,mixed> $classes
     * @return $this
     */
    public function withClasses(array $classes): self
    {
        foreach ($classes as $class => $value) {
            $this->withClass($class, $value);
        }
        return $this;
    }

    /**
     * @param array<string,mixed> $parameters
     * @return $this
     */
    public function withParameters(array $parameters): self
    {
        foreach ($parameters as $name => $value) {
            $this->withParameter($name, $value);
        }
        return $this;
    }

    /**
     * @param callable|null $callback
     * @return $this
     */
    public function resolved(?callable $callback): self
    {
        $this->resolutionCallback = $callback;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getResolutionCallback(): ?callable
    {
        return $this->resolutionCallback;
    }
}
