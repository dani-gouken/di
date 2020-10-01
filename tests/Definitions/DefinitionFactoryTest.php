<?php

namespace Atom\DI\Tests\Definitions;

use Atom\DI\Definitions\BuildObject;
use Atom\DI\Definitions\CallableDefinitionFactory;
use Atom\DI\Definitions\DefinitionFactory;
use Atom\DI\Definitions\Get;
use Atom\DI\Definitions\Value;
use Atom\DI\Definitions\Wildcard;
use Atom\DI\Tests\BaseTestCase;
use Atom\DI\Tests\Misc\Dummy1;

class DefinitionFactoryTest extends BaseTestCase
{
    public function makeFactory():DefinitionFactory
    {
        return new DefinitionFactory();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(
            BuildObject::class,
            $definition = $this->makeFactory()->instanceOf("foo")
        );
        $this->assertEquals("foo", $definition->getExtractionParameter()->getClassName());
    }

    public function testGet()
    {
         $this->assertInstanceOf(
             Get::class,
             $definition = $this->makeFactory()->get("foo")
         );
        $this->assertEquals("foo", $definition->getExtractionParameter()->getExtractionKey());
    }

    public function testValue()
    {
         $this->assertInstanceOf(
             Value::class,
             $definition = $this->makeFactory()->value("foo")
         );
        $this->assertEquals("foo", $definition->getExtractionParameter()->getValue());
    }

    public function testObject()
    {
         $this->assertInstanceOf(
             Value::class,
             $definition = $this->makeFactory()->object($object = new Dummy1())
         );
        $this->assertEquals($object, $definition->getExtractionParameter()->getValue());
    }

    public function testWildcard()
    {
         $this->assertInstanceOf(
             Wildcard::class,
             $definition = $this->makeFactory()->wildcardFor("foo")
         );
        $this->assertEquals("foo", $definition->getReplacement());
    }

    public function testCallTo()
    {
         $this->assertInstanceOf(
             CallableDefinitionFactory::class,
             $definition = $this->makeFactory()->callTo("foo")
         );
    }
}
