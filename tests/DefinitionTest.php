<?php

namespace Atom\DI\Tests;

use Atom\DI\Definition;
use Atom\DI\Definitions\BuildObject;
use Atom\DI\CallableDefinitionFactory;
use Atom\DI\Definitions\Get;
use Atom\DI\Definitions\Value;
use Atom\DI\Tests\Misc\Dummy1;

class DefinitionTest extends BaseTestCase
{
    public function makeFactory(): Definition
    {
        return new Definition();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(
            BuildObject::class,
            $definition = Definition::newInstanceOf("foo")
        );
        $this->assertEquals("foo", $definition->getClassName());
    }

    public function testGet()
    {
        $this->assertInstanceOf(
            Get::class,
            $definition = Definition::get("foo")
        );
        $this->assertEquals("foo", $definition->getKey());
    }

    public function testValue()
    {
        $this->assertInstanceOf(
            Value::class,
            $definition = Definition::value("foo")
        );
        $this->assertEquals("foo", $definition->getValue());
    }

    public function testObject()
    {
        $this->assertInstanceOf(
            Value::class,
            $definition = Definition::object($object = new Dummy1())
        );
        $this->assertEquals($object, $definition->getValue());
    }

    public function testCallTo()
    {
        $this->assertInstanceOf(
            CallableDefinitionFactory::class,
            $definition = Definition::callTo("foo")
        );
    }
}
