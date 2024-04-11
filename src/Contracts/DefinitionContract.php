<?php


namespace Atom\DI\Contracts;

use Atom\DI\Container;

interface DefinitionContract
{
    public function getResolutionCallback(): ?callable;

    public function getClass(string $className): mixed;

    public function getParameter(string $parameterName): mixed;

    public function withParameter(string $parameterName, mixed $value): self;

    public function withClass(string $className, mixed $value): self;

    public function interpret(Container $container): mixed;
}
