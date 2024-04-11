<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;

class Value extends AbstractDefinition
{
    public function __construct(private mixed $value)
    {
        $this->value = $value;
    }

    public function interpret(Container $container): mixed
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
