<?php


namespace Atom\DI\Exceptions;

use Throwable;

class MultipleBindingException extends ContainerException
{
    public function __construct(public readonly string $alias)
    {
        parent::__construct("A binding already exists for alias [{$this->alias}]");
    }
}
