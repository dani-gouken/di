<?php

namespace Atom\DI\Tests\Definitions;

use Atom\DI\Definitions\AbstractDefinition;
use Atom\DI\Extraction\ExtractionParameters\ValueExtractionParameter;
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

    public function testWith()
    {
        $definition = $this->makeDefinition();
        $definition->expects($this->any())
            ->method('getExtractionParameter')
            ->will($this->returnValue(new ValueExtractionParameter("foo")));
        $definition->with("foo", $value = new Value("bar"));
        $this->assertEquals(
            'bar',
            $definition->getExtractionParameter()
                ->getObjectMapping()->getMappingFor("foo")
                ->getDefinition()->getValue()
        );
    }

    public function testWithParameter()
    {
        $definition = $this->makeDefinition();
        $definition->expects($this->any())
            ->method('getExtractionParameter')
            ->will($this->returnValue(new ValueExtractionParameter("foo")));
        $definition->withParameter("foo", $value = new Value("bar"));
        $this->assertEquals(
            'bar',
            $definition->getExtractionParameter()
                ->getParameterMapping()->getMappingFor("foo")
                ->getDefinition()->getValue()
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
