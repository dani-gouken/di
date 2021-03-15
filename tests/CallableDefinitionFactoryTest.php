<?php


namespace Atom\DI\Tests;

use Atom\DI\CallableDefinitionFactory;
use Atom\DI\Definitions\CallFunction;
use Atom\DI\Definitions\CallMethod;

class CallableDefinitionFactoryTest extends BaseTestCase
{
    public function makeFactory($callable = null): CallableDefinitionFactory
    {
        return new CallableDefinitionFactory($callable ?? function () {
        });
    }

    public function testFunction()
    {
        $this->assertInstanceOf(CallFunction::class, $this->makeFactory()->function());
    }

    public function testMethod()
    {
        $this->assertInstanceOf(CallMethod::class, $this->makeFactory("foo")->method());
    }
}
