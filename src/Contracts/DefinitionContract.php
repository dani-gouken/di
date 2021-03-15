<?php


namespace Atom\DI\Contracts;

use Atom\DI\Container;

interface DefinitionContract
{
    public function getResolutionCallback(): ?callable;

    public function getClass(string $className);

    public function getParameter(string $parameterName);

    public function withParameter(string $parameterName, $value);

    public function withClass(string $className, $value);

    public function interpret(Container $container);
}
