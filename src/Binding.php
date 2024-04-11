<?php


namespace Atom\DI;

use Atom\DI\Contracts\BindingContract;
use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Definitions\BuildObject;
use Atom\DI\Definitions\NullDefinition;
use Atom\DI\Definitions\Value;
use RuntimeException;

class Binding implements BindingContract
{
    /**
     * @var DefinitionContract
     */
    private $definition;

    /**
     * @var string
     */
    private ?string $scope = null;

    public function __construct(?DefinitionContract $definition = null, string $scope = BindingContract::SCOPE_SINGLETON)
    {
        $this->definition = $definition ?? new NullDefinition();
        $this->scope = $scope;
    }


    public function getDefinition(): DefinitionContract
    {
        return $this->definition;
    }

    public function getScope(): string
    {
        return $this->scope ?? static::SCOPE_SINGLETON;
    }

    private function hasScope(): bool
    {
        return $this->scope != null;
    }

    private function setScope(string $scope): Binding
    {
        $this->scope = $scope;
        return $this;
    }

    public function singleton(): Binding
    {
        return $this->setScope(static::SCOPE_SINGLETON);
    }


    public function prototype(): Binding
    {
        return $this->setScope(static::SCOPE_PROTOTYPE);
    }

    public function isSingleton(): bool
    {
        return $this->getScope() == static::SCOPE_SINGLETON;
    }

    /**
     * @param string $alias
     * @param Container $container
     * @return mixed|null
     */
    public function getValue(string $alias, Container $container): mixed
    {
        $result = $container->interpret($this->getDefinition());
        if ($this->isSingleton()) {
            $container->addResolvedValue($alias, $result);
        }
        return $result;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string,mixed> $constructorParameters
     * @return DefinitionContract|BuildObject<T>
     */
    public function toInstanceOf(string $className, array $constructorParameters = []): DefinitionContract
    {
        return $this->setDefinition(Definition::newInstanceOf($className, $constructorParameters));
    }

    /**
     * @param array<string,mixed> $constructorParameters
     * @return BuildObject|DefinitionContract
     */
    public function toNewInstance($constructorParameters = []): DefinitionContract
    {
        foreach ($constructorParameters as $alias => $value) {
            $this->definition =  $this->definition->withParameter($alias, $value);
        }
        return $this->definition;
    }

    /**
     * @param mixed $value
     */
    public function toValue(mixed $value): Value
    {
        $definition = Definition::value($value);
        $this->setDefinition($definition);
        return $definition;
    }

    /**
     * @param object $value
     */
    public function toObject(object $value): Value
    {
        return $this->toValue($value);
    }

    /**
     * @param string $method
     * @param array<string,mixed> $parameters
     * @return Definitions\CallMethod|DefinitionContract
     */
    public function toMethod(string $method, array $parameters = []): DefinitionContract
    {
        return $this->setDefinition(Definition::callTo($method, $parameters)->method());
    }

    /**
     * @return Definitions\CallFunction|DefinitionContract
     */
    public function toFunction(\Closure|string|callable $function): DefinitionContract
    {
        return $this->setDefinition(Definition::callTo($function)->function());
    }

    private function setDefinition(DefinitionContract $definition): DefinitionContract
    {
        $this->definition = $definition;
        return $definition;
    }
}
