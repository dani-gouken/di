<?php


namespace Atom\DI\Tests\Definitions;

use Atom\DI\Definitions\CallableDefinitionFactory;
use Atom\DI\Definitions\CallFunction;
use Atom\DI\Definitions\CallMethod;
use Atom\DI\Tests\BaseTestCase;

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
