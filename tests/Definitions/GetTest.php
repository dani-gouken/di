<?php

namespace Atom\DI\Tests\Definitions;

use Atom\DI\Container;
use Atom\DI\Definitions\Get;
use Atom\DI\Tests\BaseTestCase;

class GetTest extends BaseTestCase
{
    public function testInterpret()
    {
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->expects($this->once())->method("getDependency")
            ->with("foo");
        $def = new Get("foo");
        $def->interpret($container);

        $container = new Container();
        $container->bind("bar")->toValue("baz");
        $def = new Get("bar");
        $this->assertEquals($def->interpret($container), "baz");
    }
}
