<?php


namespace Atom\DI;

use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Definitions\CallFunction;
use Atom\DI\Definitions\CallMethod;

class CallableDefinitionFactory
{

    /**
     * @param \Closure|callable|string $callable
     * @param array<string,mixed> $parameters
     */
    public function __construct(private mixed $callable, private array $parameters = [])
    {
        $this->callable = $callable;
        $this->parameters = $parameters;
    }

    public function function(): CallFunction
    {
        if (!is_string($this->callable) && !($this->callable instanceof \Closure)) {
            throw new \InvalidArgumentException('the function is expected to be a string(function name) or a closure');
        }
        return new CallFunction($this->callable, $this->parameters);
    }

    public function method(): CallMethod
    {
        if (!is_string($this->callable)) {
            throw new \InvalidArgumentException('the method name is expected to be a string');
        }
        return new CallMethod($this->callable, $this->parameters);
    }
}
