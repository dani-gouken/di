<?php


namespace Atom\DI\Exceptions;

use Throwable;

class MultipleBindingException extends ContainerException
{
    private $alias;

    public function __construct(string $alias)
    {
        $this->alias = $alias;
        parent::__construct("A binding already exists for alias [$alias]");
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }
}
