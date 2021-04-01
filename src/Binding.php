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

    public function __construct(?DefinitionContract $definition = null)
    {
        $this->definition = $definition ?? new NullDefinition();
    }


    public function getDefinition(): DefinitionContract
    {
        return $this->definition;
    }

    public function getScope(): string
    {
        return $this->scope ?? self::SCOPE_SINGLETON;
    }

    private function hasScope(): bool
    {
        return $this->scope != null;
    }

    private function setScope(string $scope): Binding
    {
        if ($this->hasScope()) {
            throw new RuntimeException("this definitions already has a scope({$this->scope})");
        }
        $this->scope = $scope;
        return $this;
    }

    public function singleton(): Binding
    {
        return $this->setScope(self::SCOPE_SINGLETON);
    }


    public function prototype(): Binding
    {
        return $this->setScope(self::SCOPE_PROTOTYPE);
    }

    public function isSingleton(): bool
    {
        return $this->getScope() == self::SCOPE_SINGLETON;
    }

    /**
     * @param string $alias
     * @param Container $container
     * @return mixed|null
     */
    public function getValue(string $alias, Container $container)
    {
        $result = $container->interpret($this->getDefinition());
        if ($this->isSingleton()) {
            $container->addResolvedValue($alias, $result);
        }
        return $result;
    }

    /**
     * @param string $className
     * @param array $constructorParameters
     * @return DefinitionContract|BuildObject
     */
    public function toInstanceOf(string $className, $constructorParameters = []): BuildObject
    {
        return $this->setDefinition(Definition::newInstanceOf($className, $constructorParameters));
    }

    /**
     * @param array $constructorParameters
     * @return BuildObject|DefinitionContract
     */
    public function toNewInstance($constructorParameters = []): BuildObject
    {
        return $this->definition->withParameters($constructorParameters);
    }

    /**
     * @param mixed $value
     * @return DefinitionContract|Value
     */
    public function toValue($value): Value
    {
        return $this->setDefinition(Definition::value($value));
    }

    /**
     * @param object $value
     * @return Value|DefinitionContract
     */
    public function toObject(object $value): Value
    {
        return $this->toValue($value);
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return Definitions\CallMethod|DefinitionContract
     */
    public function toMethod(string $method, array $parameters = []): Definitions\CallMethod
    {
        return $this->setDefinition(Definition::callTo($method, $parameters)->method());
    }

    /**
     * @param $function
     * @return Definitions\CallFunction|DefinitionContract
     */
    public function toFunction($function): Definitions\CallFunction
    {
        return $this->setDefinition(Definition::callTo($function)->function());
    }

    private function setDefinition(DefinitionContract $definition): DefinitionContract
    {
        $this->definition = $definition;
        return $definition;
    }
}
