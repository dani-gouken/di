<?php


namespace Atom\DI\Exceptions;

use Exception;
use Atom\DI\ResolutionStack;

class CircularDependencyException extends Exception
{
    public function __construct(string $alias, ResolutionStack $chain)
    {
        parent::__construct("The definition of [$alias] is not valid, because it depends on a lookup 
        of itself in the container. Chain: [ $chain => $alias ]");
    }
}
