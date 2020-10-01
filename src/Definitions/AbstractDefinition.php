<?php


namespace Atom\DI\Definitions;

use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Mapping\MappingItem;

abstract class AbstractDefinition implements DefinitionContract
{
    /**
     * @var callable
     */
    protected $resolutionCallback;

    /**
     * @param string $className
     * @param DefinitionContract $definition
     * @return AbstractDefinition
     */
    public function with(string $className, DefinitionContract $definition): self
    {
        $this->getExtractionParameter()
            ->getObjectMapping()
            ->add(new MappingItem($className, $definition));
        return $this;
    }

    public function withBindings(array $bindings): self
    {
        foreach ($bindings as $name => $binding) {
            $this->with($name, $binding);
        }
        return $this;
    }

    /**
     * @param string $parameterName
     * @param $definition
     * @return AbstractDefinition
     */
    public function withParameter(string $parameterName, $definition): self
    {
        $this->getExtractionParameter()->getParameterMapping()
            ->add(new MappingItem(
                $parameterName,
                $definition instanceof DefinitionContract ? $definition : new Value($definition)
            ));
        return $this;
    }

    /**
     * @param array $parameters
     * @return AbstractDefinition
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
