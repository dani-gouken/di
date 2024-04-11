<?php


namespace Atom\DI\Tests;

use Atom\DI\Binding;
use Atom\DI\Container;
use Atom\DI\Contracts\BindingContract;
use Atom\DI\Definitions\BuildObject;
use Atom\DI\Definitions\CallFunction;
use Atom\DI\Definitions\CallMethod;
use Atom\DI\Definitions\NullDefinition;
use Atom\DI\Definitions\Value;
use Atom\DI\Tests\Misc\Dummy1;

class BindingTest extends BaseTestCase
{
    public function testConstructor()
    {
        $container = new Container();
        $binding = new Binding();
        $this->assertInstanceOf(NullDefinition::class, $binding->getDefinition());
        $this->assertNull($binding->getDefinition()->interpret($container));
        $binding = new Binding($def = new Value("bar"));
        $this->assertEquals($def, $binding->getDefinition());
    }

    public function testScope()
    {
        $binding = new Binding();
        $this->assertEquals(BindingContract::SCOPE_SINGLETON, $binding->getScope());
        $binding->prototype();
        $this->assertEquals(BindingContract::SCOPE_PROTOTYPE, $binding->getScope());
        $binding = new Binding();
        $binding->singleton();
        $this->assertTrue($binding->isSingleton());
        $this->assertEquals(BindingContract::SCOPE_SINGLETON, $binding->getScope());
    }

    public function testGetValue()
    {
        $binding = new Binding();
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->expects($this->once())->method("interpret")
            ->with($binding->getDefinition());
        $container->expects($this->once())->method("addResolvedValue")
            ->with("foo", null);
        $binding->getValue("foo", $container);

        $binding = (new Binding())->prototype();
        $def = $binding->toValue("42");
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->expects($this->once())->method("interpret")
            ->with($def);
        $container->expects($this->never())->method("addResolvedValue")
            ->with("foo", null);
        $binding->getValue("foo", $container);
        $this->assertEquals("42", $binding->getValue("foo", new Container()));
    }

    public function testDefinitionFactory()
    {
        $binding = new Binding();
        $this->assertInstanceOf(NullDefinition::class, $binding->getDefinition());

        $binding->toValue(42);
        $this->assertInstanceOf(Value::class, $binding->getDefinition());
        $this->assertEquals(42, $binding->getDefinition()->getValue());

        $binding->toInstanceOf("baz", ["foo" => "bar"]);
        $this->assertInstanceOf(BuildObject::class, $def = $binding->getDefinition());
        $this->assertEquals("baz", $binding->getDefinition()->getClassName());
        $this->assertEquals("bar", $binding->getDefinition()->getParameter("foo"));
        $this->assertEquals($def, $binding->toNewInstance());

        $binding->toObject($obj = new Dummy1());
        $this->assertInstanceOf(Value::class, $binding->getDefinition());
        $this->assertEquals($obj, $binding->getDefinition()->getValue());

        $binding->toFunction($fn = function () {
        });
        $this->assertInstanceOf(CallFunction::class, $binding->getDefinition());
        $this->assertEquals($fn, $binding->getDefinition()->getCallable());

        $binding->toMethod("baz", ["foo"=>"bar"])->on("foo");
        $this->assertInstanceOf(CallMethod::class, $binding->getDefinition());
        $this->assertEquals("baz", $binding->getDefinition()->getMethod());
        $this->assertEquals("foo", $binding->getDefinition()->getObject());
        $this->assertEquals("bar", $binding->getDefinition()->getParameter("foo"));
    }
}
