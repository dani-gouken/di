<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;

class Value extends AbstractDefinition
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function interpret(Container $container)
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
