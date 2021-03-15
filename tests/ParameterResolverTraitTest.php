<?php

namespace Atom\DI\Tests;

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\ParameterResolverTrait;
use Atom\DI\Tests\Misc\Dummy1;
use Atom\DI\Tests\Misc\Dummy2;
use Atom\DI\Tests\Misc\Dummy3;
use ReflectionException;
use ReflectionFunction;

class ParameterResolverTraitTest extends BaseTestCase
{
    /**
     * @return ParameterResolverTrait
     */
    public function makeParameterResolver()
    {
        /**
         * @var $trait ParameterResolverTrait
         */
        $trait = $this->getObjectForTrait(ParameterResolverTrait::class);
        return $trait;
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testSearchParameterValueWithDefaultValue()
    {
        $resolver = $this->makeParameterResolver();
        $reflectedFunction = new ReflectionFunction("Atom\\DI\\Tests\\Misc\\returnDefaultValue");
        $parameter = $reflectedFunction->getParameters()[0];
        $value = $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $this->getContainer(),
            []
        );
        $this->assertEquals("DefaultValue", $value);
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testSearchParameterValueUsingParameterMapping()
    {
        $resolver = $this->makeParameterResolver();
        $reflectedFunction = new ReflectionFunction("Atom\\DI\\Tests\\Misc\\returnValue");
        $parameter = $reflectedFunction->getParameters()[0];
        $value = $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $this->getContainer(),
            ["value" => "bar"]
        );
        $this->assertEquals("bar", $value);
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testSearchParameterValueUsingClassMapping()
    {
        $resolver = $this->makeParameterResolver();
        $reflectedFunction = new ReflectionFunction("Atom\\DI\\Tests\\Misc\\returnDummy2");
        $parameter = $reflectedFunction->getParameters()[0];
        $value = $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $this->getContainer(),
            [],
            [Dummy2::class => new Dummy2("bar")]
        );
        $this->assertInstanceOf(Dummy2::class, $value);
        $this->assertEquals("bar", $value->getFoo());
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testSearchParameterValueUsingAutoWiring()
    {
        $resolver = $this->makeParameterResolver();
        $reflectedFunction = new ReflectionFunction("Atom\\DI\\Tests\\Misc\\returnDummy1");
        $parameter = $reflectedFunction->getParameters()[0];
        $value = $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $this->getContainer(),
        );
        $this->assertInstanceOf(Dummy1::class, $value);
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testSearchParameterValueUsingTheContainer()
    {
        $resolver = $this->makeParameterResolver();
        $reflectedFunction = new ReflectionFunction("Atom\\DI\\Tests\\Misc\\returnDummy2");
        $container = $this->getContainer();
        $container->bind(Dummy2::class)->toNewInstance()
            ->withParameter("foo", "bar");
        $parameter = $reflectedFunction->getParameters()[0];
        $value = $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $container,
        );
        $this->assertInstanceOf(Dummy2::class, $value);
        $this->assertEquals("bar", $value->getFoo());
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testItThrowsIfItCantResolveTheParameter()
    {
        $resolver = $this->makeParameterResolver();
        $reflectedFunction = new ReflectionFunction("Atom\\DI\\Tests\\Misc\\returnValue");
        $parameter = $reflectedFunction->getParameters()[0];
        $this->expectException(ContainerException::class);
        $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $this->getContainer(),
        );
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testItThrowsIfItCantResolveTheParameterMethod()
    {
        $resolver = $this->makeParameterResolver();
        $reflectedFunction = new \ReflectionMethod(new Dummy3(), "getBar");
        $parameter = $reflectedFunction->getParameters()[0];
        $this->expectException(ContainerException::class);
        $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $this->getContainer(),
        );
    }

    public function testItStoreResolvedParameters()
    {
        $resolver = $this->makeParameterResolver();
        $reflectedFunction = new ReflectionFunction("Atom\\DI\\Tests\\Misc\\returnDummy2");
        $parameter = $reflectedFunction->getParameters()[0];
        $container = $this->getContainer();
        $container->prototype(Dummy2::class)->toNewInstance(["foo" => "foo"]);
        $res = $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $container,
            [],
        );
        $this->assertInstanceOf(Dummy2::class, $res);
        $this->assertEquals($res, $resolver->searchParameterValue(
            $reflectedFunction,
            $parameter,
            $container,
            [],
        ));
    }
}
