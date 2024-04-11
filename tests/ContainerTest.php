<?php

namespace Atom\DI\Tests;

use Atom\DI\Binding;
use Atom\DI\Container;
use Atom\DI\Contracts\BindingContract;
use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Definition;
use Atom\DI\Definitions\BuildObject;
use Atom\DI\Definitions\Value;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\MultipleBindingException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\ResolutionStack;
use Atom\DI\Tests\Misc\CircularDependency\CDDummy2;
use Atom\DI\Tests\Misc\Dummy1;
use Atom\DI\Tests\Misc\Dummy2;
use Atom\DI\Tests\Misc\Dummy3;
use Psr\Container\ContainerInterface;
use ReflectionException;

class ContainerTest extends BaseTestCase
{
    /**
     * @group  ContainerTest
     */
    public function testTheContainerCanBeInstantiated()
    {
        $container = $this->getContainer();
        $this->assertInstanceOf(Container::class, $container);
        $this->assertInstanceOf(ResolutionStack::class, $container->getResolutionStack());
        $this->assertEmpty($container->getResolutionStack()->getStack());
    }

    public function testItImplementPsr4()
    {
        $container = $this->getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws MultipleBindingException
     * @throws ReflectionException
     */
    public function testBind()
    {
        $container = $this->getContainer();
        $binding = $container->bind("foo");
        $this->assertInstanceOf(BindingContract::class, $binding);
        $this->assertInstanceOf(Binding::class, $binding);
        $this->assertInstanceOf(BuildObject::class, $binding->getDefinition());
        $this->assertEquals("foo", $binding->getDefinition()->getClassName());

        $container = $this->getContainer();
        $binding = $container->bind("foo", "bar");
        $this->assertInstanceOf(Value::class, $binding->getDefinition());
        $this->assertEquals("bar", $binding->getDefinition()->getValue());

        $container = $this->getContainer();
        $binding = $container->bind("foo", $obj = new Dummy1());
        $this->assertInstanceOf(Value::class, $binding->getDefinition());
        $this->assertEquals($obj, $binding->getDefinition()->getValue());

        $container = $this->getContainer();
        $binding = $container->bind("foo", 42);
        $this->assertInstanceOf(Value::class, $binding->getDefinition());
        $this->assertEquals(42, $binding->getDefinition()->getValue());

        $container = $this->getContainer();
        $container->bind(["foo", "bar"])->toValue(42);
        $this->assertEquals(42, $container->get("foo"));
        $this->assertEquals(42, $container->get("bar"));

        $container = $this->getContainer();
        $container->bind("bar", new Value(69));
        $this->assertEquals(69, $container->get("bar"));

        $container = $this->getContainer();
        $container->bind("bar", new Value(69));
        $this->assertEquals(69, $container->get("bar"));
        $this->assertTrue($binding->isSingleton());
    }

    /**
     * @throws MultipleBindingException
     */
    public function testBindIfNotAvailable()
    {
        $container = $this->getContainer();
        $binding = $container->bind("foo");
        $this->assertInstanceOf(BindingContract::class, $binding);
        $this->assertInstanceOf(Binding::class, $binding);
        $this->assertInstanceOf(BuildObject::class, $binding->getDefinition());
        $this->assertEquals("foo", $binding->getDefinition()->getClassName());

        $this->assertEquals($container->bindIfNotAvailable("foo"), $binding);
    }

    /**
     * @throws MultipleBindingException
     */
    public function testItThrowsOnMultipleBinding()
    {
        $container = $this->getContainer();
        $this->expectException(MultipleBindingException::class);
        $container->bind("foo");
        $container->bind("foo");
    }

    public function testAliasWhenItThrowsOnMultipleBinding()
    {
        $container = $this->getContainer();
        try {
            $container->bind("foo");
            $container->bind("foo");
        } catch (MultipleBindingException $e) {
            $this->assertEquals("foo", $e->alias);
        }
    }

    /**
     * @throws MultipleBindingException
     */
    public function testPrototype()
    {
        $this->assertEquals(
            BindingContract::SCOPE_PROTOTYPE,
            $this->getContainer()->prototype("foo")->getScope()
        );
    }

    public function testGet()
    {
        $container = $this->getContainer();
        $container->getResolutionStack()->append("foo");
        $container->bind("foo")->toValue("bar");
        $this->assertEquals("bar", $container->get("foo"));
        $this->assertEmpty($container->getResolutionStack()->getStack());
    }

    public function testGetDependency()
    {
        $container = $this->getContainer();
        $container->addResolvedValue("foo", "bar");
        $this->assertEquals("bar", $container->getDependency("foo"));
        $this->assertEquals(["foo"], $container->getResolutionStack()->getStack());

        $container = $this->getContainer();
        $container->bind("foo")->toValue(42);
        $this->assertEquals(42, $container->getDependency("foo"));

        $container = $this->getContainer();
        $this->assertInstanceOf(
            Dummy1::class,
            $container->getDependency(Dummy1::class)
        );
        $this->assertEquals([Dummy1::class], $container->getResolutionStack()->getStack());

        $this->expectException(NotFoundException::class);
        $container = $this->getContainer();
        $container->get("bar", [], false);
    }

    public function testInvalidCallback()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getContainer()->resolved(true);
    }

    public function testResolutionCallBacksAreCalled()
    {
        $container = $this->getContainer();
        $container->resolved(function ($alias) {
            return $alias;
        });
        $container->bind("foo")->toValue("baz");
        $this->assertEquals("foo", $container->get("foo"));
        $container = $this->getContainer();
        $container->bind("foo")->toValue("bar");
        $container->bind("bar")->toValue("baz");
        $container->resolved("foo", function () {
            return "foo";
        });
        $this->assertEquals("foo", $container->get("foo"));
        $this->assertEquals("baz", $container->get("bar"));

        $container = $this->getContainer();
        $container->bind("foo")->toValue("bar")->resolved(function () {
            return "baz";
        });

        $this->assertEquals("baz", $container->get("foo"));
    }

    public function testInterpret()
    {
        $container = new Container();
        $definition = $this->getMockBuilder(DefinitionContract::class)->getMock();
        $definition->expects($this->once())->method("interpret")->with($container);
        $definition->expects($this->once())->method("getResolutionCallback")->willReturn(null);
        $container->interpret($definition);

        $container = new Container();
        $definition = Definition::value("fizz")->resolved(function ($value, $container) {
            return $value . $container->get("bar");
        });
        $container->bind("bar")->toValue("buzz");
        $this->assertEquals("fizzbuzz", $container->interpret($definition));
    }

    public function testHas()
    {
        $container = $this->getContainer();
        $this->assertFalse($container->has("foo"));
        $container->bind("foo");
        $this->assertTrue($container->has("foo"));
    }

    public function testRemove()
    {
        $container = $this->getContainer();
        $container->remove("foo");
        $container->bind("foo")->toValue("bar");
        $this->assertTrue($container->has("foo"));
        $container->remove("foo");
        $this->assertFalse($container->has("foo"));
    }

    public function testMakeThrowsIfTheClassDoesNotExists()
    {
        $container = $this->getContainer();
        $this->expectException(ContainerException::class);
        $container->make("foo");
    }


    public function testMakeThrowsIfTheClassIsNotInstantiable()
    {
        $container = $this->getContainer();
        $this->expectException(ContainerException::class);
        $container->make(DefinitionContract::class);
    }

    public function testMake()
    {
        $container = $this->getContainer();
        $this->assertInstanceOf(Dummy1::class, $container->make(Dummy1::class));
        $this->assertInstanceOf(
            Dummy2::class,
            $dummy2 = $container->make(Dummy2::class, ["foo" => "baz"])
        );
        $this->assertEquals("baz", $dummy2->getFoo());
        $this->assertEquals("bar", $dummy2->getBar());
    }

    public function testCallFunction()
    {
        $container = $this->getContainer();
        $this->assertEquals("foo", $container->callFunction("Atom\\DI\\Tests\\Misc\\returnFoo"));
        $this->assertEquals("bar", $container->callFunction("Atom\\DI\\Tests\\Misc\\returnBar"));
        $this->assertEquals("DefaultValue", $container->callFunction("Atom\\DI\\Tests\\Misc\\returnDefaultValue"));
        $this->assertEquals(
            "foo",
            $container->callFunction("Atom\\DI\\Tests\\Misc\\returnDefaultValue", ["defaultValue" => "foo"])
        );
        $this->assertEquals(
            "foo",
            $container->callFunction("Atom\\DI\\Tests\\Misc\\returnDefaultValue", ["defaultValue" => "foo"])
        );
        $this->assertEquals(
            "bar",
            $container->callFunction("Atom\\DI\\Tests\\Misc\\returnDummy2", [], [
                Dummy2::class => Definition::newInstanceOf(Dummy2::class)->withParameter("foo", "bar")
            ])->getFoo()
        );
    }

    public function testCallMethod()
    {
        $container = $this->getContainer();
        $this->assertEquals("foo", $container->callMethod(Dummy3::class));
        $this->assertEquals("baz", $container->callMethod(
            Dummy3::class,
            "getBar",
            ["bar" => "baz"]
        ));
        $this->assertEquals("baz", $container->callMethod(
            Definition::newInstanceOf(Dummy3::class),
            "getBar",
            ["bar" => "baz"]
        ));
    }


    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testGetThrowsInCaseOfCircularDependency()
    {
        $container = $this->getContainer();
        $this->expectException(CircularDependencyException::class);
        $container->get(CDDummy2::class);
    }

    public function testArrayAccess()
    {
        $container = $this->getContainer();
        $container["foo"] = "bar";
        $this->assertEquals($container["foo"], "bar");
        $this->assertTrue($container->has('foo'));
        $this->assertTrue(isset($container["foo"]));
        unset($container["foo"]);
        $this->assertFalse($container->has('foo'));
        $this->assertFalse(isset($container["foo"]));
        $container = $this->getContainer();
        $container["foo"] = Definition::callTo(function () {
            return "bar";
        })->function();
        $this->assertEquals("bar", $container["foo"]);
    }
}
