<?php

namespace Atom\DI\Tests\Storage;

use Atom\DI\Contracts\ExtractorContract;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Extraction\ExtractionParameters\ValueExtractionParameter;
use Atom\DI\Extraction\FunctionExtractor;
use Atom\DI\Storage\AbstractStorage;
use Atom\DI\Definitions\Value;
use Atom\DI\Tests\BaseTestCase;
use TypeError;

class AbstractStorageTest extends BaseTestCase
{
    private function makeStorage(): AbstractStorage
    {
        $container = $this->getContainer();
        return $this->getMockForAbstractClass(AbstractStorage::class, [$container]);
    }

    /**
     * @throws ContainerException
     */
    public function testAddSupportForExtractor()
    {
        $storage = $this->makeStorage();
        /** @var ExtractorContract $extractor  */
        $extractor = $this->createMock(ExtractorContract::class);
        $storage->getContainer()->addExtractor($extractor);

        $this->assertFalse($storage->supportExtractor(get_class($extractor)));

        $storage->addSupportForExtractor(get_class($extractor));
        $this->assertTrue($storage->supportExtractor(get_class($extractor)));

        $this->expectException(ContainerException::class);
        $storage->addSupportForExtractor("foo");
    }

    /**
     * @throws ContainerException
     */
    public function testSupportExtractor()
    {
        $storage = $this->makeStorage();
        /** @var ExtractorContract $extractor  */
        $extractor = $this->createMock(ExtractorContract::class);
        $storage->getContainer()->addExtractor($extractor);

        $this->assertTrue($storage->supportExtractor(FunctionExtractor::class));
        $this->assertFalse($storage->supportExtractor(get_class($extractor)));
        $storage->addSupportForExtractor(get_class($extractor));
        $this->assertTrue($storage->supportExtractor(get_class($extractor)));
    }

    public function testHasAndContains()
    {
        $storage = $this->makeStorage();

        $storage->store("foo", new Value("bar"));
        $this->assertTrue($storage->has("foo"));
        $this->assertTrue($storage->contains("foo"));

        $this->assertFalse($storage->has("bar"));
        $this->assertFalse($storage->contains("bar"));
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testStore()
    {
        $storage = $this->makeStorage();
        $this->assertFalse($storage->has("foo"));
        $storage->store("foo", new Value("bar"));
        $this->assertTrue($storage->has("foo"));
        $storage->store(["bar","baz","jhon"], new Value("42"));
        $this->assertTrue($storage->has("bar"));
        $this->assertTrue($storage->has("baz"));
        $this->assertTrue($storage->has("jhon"));

        $bar = $storage->resolve("bar");
        $this->assertEquals($bar, $storage->resolve("baz"));
        $this->assertEquals($bar, $storage->resolve("jhon"));
        $this->assertEquals("42", $storage->get("bar"));
        $this->assertEquals("42", $storage->get("baz"));
        $this->assertEquals("42", $storage->get("jhon"));
    }

    /**
     * @throws NotFoundException
     */
    public function testResolve()
    {
        $storage = $this->makeStorage();
        $storage->store("foo", new Value("bar"));
        $this->assertInstanceOf(Value::class, $storage->resolve("foo"));
        /** @var ValueExtractionParameter $extractionParameter */
        $extractionParameter = $storage->resolve("foo")->getExtractionParameter();
        $this->assertEquals("bar", $extractionParameter->getValue());

        $this->expectException(NotFoundException::class);
        $storage->resolve("bar");
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testGet()
    {
        $storage = $this->makeStorage();
        $storage->store("foo", new Value("bar"));
        $this->assertEquals("bar", $storage->get("foo"));

        $this->expectException(NotFoundException::class);
        $storage->resolve("bar");
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function testExtends()
    {
        $storage = $this->makeStorage();
        $storage->store("foo", new Value("bar"));

        $storage->extends("foo", function (Value $definition) {
            $definition->setValue("baz");
            return $definition;
        });
        $this->assertEquals("baz", $storage->get("foo"));

        $this->expectException(TypeError::class);
        $storage->extends("foo", function (Value $definition) {
            $definition->setValue("baz");
            return "baz";
        });
    }

    public function testGetDescriptions()
    {
        $storage = $this->makeStorage();
        $this->assertEquals([], $storage->getDefinitions());
        $storage->store("foo", $value = new Value("bar"));
        $this->assertEquals(["foo" => 0], $storage->getBindings());
    }

    public function testRemove()
    {
        $storage = $this->makeStorage();
        $storage->store("foo", $value = new Value("bar"));
        $storage->store("bar", $value = new Value("baz"));

        $this->assertTrue($storage->has("foo"));
        $this->assertTrue($storage->has("bar"));

        $storage->remove("foo");
        $this->assertFalse($storage->has("foo"));
        $this->assertTrue($storage->has("bar"));
    }
}
