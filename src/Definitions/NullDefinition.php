<?php


namespace Atom\DI\Definitions;

use Atom\DI\Container;

class NullDefinition extends AbstractDefinition
{

    public function interpret(Container $container)
    {
        return null;
    }
}
