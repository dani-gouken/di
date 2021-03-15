<?php


namespace Atom\DI\Tests\Definitions;

use Atom\DI\Container;
use Atom\DI\Definitions\Value;
use Atom\DI\Tests\BaseTestCase;

class ValueTest extends BaseTestCase
{

    public function testInterpret()
    {
        $definition = new Value("foo");
        $container = new Container();
        $this->assertEquals("foo", $definition->interpret($container));
    }

    public function testGetValue()
    {
        $definition = new Value("foo");
        $this->assertEquals("foo", $definition->getValue());
    }

}
