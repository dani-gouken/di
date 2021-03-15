<?php


namespace Atom\DI\Tests;

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\ResolutionStack;

class ResolutionStackTest extends BaseTestCase
{

    public function testAppend()
    {
        $stack = new ResolutionStack();
        $stack->append("foo");
        $stack->append("bar");
        $this->assertEquals(["foo", "bar"], $stack->getStack());
    }

    public function testItThrowsIfAndItemIsAddedMultipleTime()
    {
        $this->expectException(CircularDependencyException::class);
        $stack = new ResolutionStack();
        $stack->append("foo");
        $stack->append("foo");
    }

    public function testContains()
    {
        $stack = new ResolutionStack();
        $stack->append("foo");
        $this->assertTrue($stack->contains("foo"));
        $this->assertFalse($stack->contains("baz"));
    }

    public function testClear()
    {
        $stack = new ResolutionStack();
        $stack->append("foo");
        $stack->append("bar");
        $this->assertEquals(["foo", "bar"], $stack->getStack());
        $stack->clear();
        $this->assertEquals([], $stack->getStack());
    }

    public function testPop()
    {
        $stack = new ResolutionStack();
        $stack->append("foo");
        $stack->append("bar");
        $this->assertEquals(["foo", "bar"], $stack->getStack());
        $stack->pop();
        $this->assertEquals(["foo"], $stack->getStack());
    }
}
