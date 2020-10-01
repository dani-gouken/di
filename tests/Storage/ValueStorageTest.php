<?php

namespace Atom\DI\Tests\Storage;

use Atom\DI\Definitions\BuildObject;
use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\UnsupportedInvokerException;
use Atom\DI\Extraction\FunctionExtractor;
use Atom\DI\Storage\ValueStorage;
use Atom\DI\Tests\BaseTestCase;
use Atom\DI\Definitions\Value;
use Atom\DI\Tests\Misc\Dummy1;
use PHPUnit\Framework\MockObject\MockObject;

class ValueStorageTest extends BaseTestCase
{
    private function makeStorage(): ValueStorage
    {
        return new ValueStorage($this->getContainer());
    }
    public function testStorageKey()
    {
        $this->assertEquals("BINDINGS", $this->makeStorage()->getStorageKey());
    }

    /**
     * @throws UnsupportedInvokerException
     */
    public function testStoreWithInvalidExtractor()
    {
        $definition = $this->createMock(DefinitionContract::class);
        /** @var DefinitionContract|MockObject  $definition */
        $definition->method("getExtractorClassName")->willReturn(FunctionExtractor::class);

        $this->expectException(UnsupportedInvokerException::class);
        $this->makeStorage()->store("foo", $definition);
    }

    /**
     * @throws UnsupportedInvokerException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testStore()
    {
        $storage = $this->makeStorage();
        $this->assertCount(0, $storage->getDefinitions());

        $storage->store("bar", new Value("baz"));
        $storage->store("foo", new BuildObject(Dummy1::class));

        $this->assertCount(2, $storage->getDefinitions());
        $this->assertInstanceOf(Dummy1::class, $storage->get("foo"));
        $this->assertEquals("baz", $storage->get("bar"));
    }
}
