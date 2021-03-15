<?php


namespace Atom\DI\Tests\Definitions;

use Atom\DI\Container;
use Atom\DI\Definitions\BuildObject;
use Atom\DI\Tests\BaseTestCase;
use Atom\DI\Tests\Misc\Dummy2;

class BuildObjectTest extends BaseTestCase
{
    public function testItCanBeInstantiated()
    {
        $this->assertInstanceOf(BuildObject::class, $obj = new BuildObject("foo", [
            "foo" => "bar",
            "bar" => "baz"
        ]));
        $this->assertEquals("bar", $obj->getParameter("foo"));
        $this->assertEquals("baz", $obj->getParameter("bar"));
        $this->assertEquals("foo", $obj->getClassName());
    }

    public function testInterpret()
    {
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->expects($this->once())->method("make")
            ->with("foo", $params = ["foo" => "bar"], $classes = ["bar" => "baz"]);

        $def = (new BuildObject("foo", $params))->withClasses($classes);
        $def->interpret($container);

        $container = new Container();
        $def = (new BuildObject(Dummy2::class, ["foo"=>"bar","bar"=>"baz"]));
        /**
         * @var Dummy2 $res
         */
        $res = $def->interpret($container);
        $this->assertInstanceOf(Dummy2::class, $res);
        $this->assertEquals("bar", $res->getFoo());
        $this->assertEquals("baz", $res->getBar());
    }
}
