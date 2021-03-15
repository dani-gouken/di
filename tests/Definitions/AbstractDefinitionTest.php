<?php

namespace Atom\DI\Tests\Definitions;

use Atom\DI\Definitions\AbstractDefinition;
use Atom\DI\Definitions\Value;
use Atom\DI\Tests\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AbstractDefinitionTest extends BaseTestCase
{
    /**
     * @return AbstractDefinition | MockObject
     */
    public function makeDefinition(): AbstractDefinition
    {
        return $this->getMockForAbstractClass(AbstractDefinition::class);
    }

    public function testWithClass()
    {
        $definition = $this->makeDefinition();
        $definition->withClass("foo", $value = new Value("bar"));
        $this->assertEquals(
            $value,
            $definition->getClass("foo")
        );
    }

    public function testWithParameter()
    {
        $definition = $this->makeDefinition();
        $definition->withParameter("foo", "bar");
        $this->assertEquals(
            'bar',
            $definition->getParameter("foo")
        );
    }

    public function testGetResolutionCallback()
    {
        $definition = $this->makeDefinition();
        $this->assertNull($definition->getResolutionCallback());
        $definition->resolved(function () {
            return "foo";
        });
        $this->assertEquals("foo", $definition->getResolutionCallback()());
    }
}
