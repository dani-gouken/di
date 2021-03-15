<?php

namespace Atom\DI\Tests\Definitions;

use Atom\DI\Container;
use Atom\DI\Definitions\CallFunction;
use Atom\DI\Tests\BaseTestCase;
use function Atom\DI\Tests\Misc\returnDefaultValue;

class CallFunctionTest extends BaseTestCase
{
    private function makeDefinition(array $params = [], $callable = null): CallFunction
    {
        return new CallFunction($callable ?? function () {
                return "foo";
        }, $params);
    }

    public function testConstructor()
    {
        $def = $this->makeDefinition(["foo" => "bar"], $c = function () {
            return "bar";
        });
        $this->assertEquals("bar", $def->getParameter("foo"));
        $this->assertEquals($c, $def->getCallable());
        $this->assertEquals("bar", $c());
    }

    public function testInterpret()
    {
        $c = function () {
            return "bar";
        };
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->expects($this->once())->method("callFunction")
            ->with($c, $params = ["foo" => "bar"]);

        $def = $this->makeDefinition($params, $c);
        $def->interpret($container);

        $container = new Container();
        $def = $this->makeDefinition(
            ["defaultValue" => $default = "foobarbaz"],
            "Atom\\DI\\Tests\\Misc\\returnDefaultValue"
        );
        $this->assertEquals($default, $def->interpret($container));
    }
}
