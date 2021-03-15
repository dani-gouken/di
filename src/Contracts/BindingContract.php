<?php


namespace Atom\DI\Contracts;

use Atom\DI\Container;

interface BindingContract
{
    const SCOPE_SINGLETON = "SINGLETON";
    const SCOPE_PROTOTYPE = "PROTOTYPE";
    const SCOPES = [self::SCOPE_PROTOTYPE, self::SCOPE_SINGLETON];

    /**
     * @return DefinitionContract
     */
    public function getDefinition(): DefinitionContract;

    /**
     * @return string
     */
    public function getScope(): string;

    public function getValue(string $alias, Container $container);
}
