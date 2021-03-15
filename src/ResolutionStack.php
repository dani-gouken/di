<?php


namespace Atom\DI;

use Atom\DI\Exceptions\CircularDependencyException;

class ResolutionStack
{
    /**
     * @var string[]
     */
    public $stack = [];

    /**
     * @param string $item
     * @throws CircularDependencyException
     */
    public function append(string $item)
    {
        if ($this->contains($item)) {
            throw new CircularDependencyException($item, $this);
        }
        $this->stack[] = $item;
    }

    public function contains(string $item): bool
    {
        return in_array($item, $this->stack);
    }


    public function clear()
    {
        $this->stack = [];
    }

    public function pop()
    {
        array_pop($this->stack);
    }

    /**
     * @return array
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    public function __toString(): string
    {
        return implode(" => ", $this->stack);
    }
}
