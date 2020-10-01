<?php


namespace Atom\DI\Tests\Mapping;

use Atom\DI\Mapping\MappingItem;
use Atom\DI\Definitions\Value;
use Atom\DI\Tests\BaseTestCase;

class MappingItemTest extends BaseTestCase
{
    public function testMappedEntityKeyAndGetDefinition()
    {
        $mappingItem = new MappingItem("foo", $definition = new Value("bar"));
        $this->assertEquals("foo", $mappingItem->getMappedEntityKey());
        $this->assertEquals($definition, $mappingItem->getDefinition());
    }
}
