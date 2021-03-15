<?php

namespace Atom\DI\Tests\Definitions;

use Atom\DI\Container;
use Atom\DI\Definitions\CallMethod;
use Atom\DI\Extraction\ExtractionParameters\MethodExtractionParameter;
use Atom\DI\Extraction\MethodExtractor;
use Atom\DI\Tests\BaseTestCase;
use Atom\DI\Tests\Misc\Dummy1;
use Atom\DI\Tests\Misc\Dummy3;

class CallMethodTest extends BaseTestCase
{
    public function makeDefinition(): CallMethod
    {
        return new CallMethod();
    }

    public function testOn()
    {
        $definition = $this->makeDefinition();
        $definition->on($instance = new Dummy1());
        $this->assertEquals($instance, $definition->getObject());
    }

    public function testConstructor()
    {
        $def = new CallMethod();
        $this->assertEquals($def->getMethod(), "__invoke");
        $def = new CallMethod("getFoo", ["bar" => "baz"]);
        $this->assertEquals("baz", $def->getParameter("bar"));
        $this->assertEquals("getFoo", $def->getMethod());
    }

    public function testInterpret()
    {
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->expects($this->once())->method("callMethod")
            ->with(Dummy3::class, $m = "getBar", $params = ["bar" => "baz"]);
        $def = (new CallMethod($m, $params))->on(Dummy3::class);
        $def->interpret($container);
        $container = new Container();
        $def = (new CallMethod("getBar", ["bar"=>"baz"]))->on(Dummy3::class);
        $this->assertEquals($def->interpret($container), "baz");
    }
}
