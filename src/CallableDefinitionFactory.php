<?php


namespace Atom\DI;

use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Definitions\CallFunction;
use Atom\DI\Definitions\CallMethod;

class CallableDefinitionFactory
{
    private array $parameters;
    private $callable;

    public function __construct($callable, array $parameters = [])
    {
        $this->callable = $callable;
        $this->parameters = $parameters;
    }

    /**
     * @return CallFunction|DefinitionContract
     */
    public function function(): CallFunction
    {
        return new CallFunction($this->callable, $this->parameters);
    }

    /**
     * @return CallMethod |DefinitionContract
     */
    public function method(): CallMethod
    {
        return new CallMethod($this->callable, $this->parameters);
    }
}
