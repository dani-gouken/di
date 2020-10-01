<?php


namespace Atom\DI\Tests\Extraction\ExtractionParameters;

use Atom\DI\Extraction\ExtractionParameters\ContainerExtractionParameter;
use Atom\DI\Tests\BaseTestCase;

class ContainerExtractionParameterTest extends BaseTestCase
{
    public function makeParameter(string $key)
    {
        return new ContainerExtractionParameter($key);
    }

    public function testItCanBeInstantiated()
    {
        $this->assertInstanceOf(ContainerExtractionParameter::class, $this->makeParameter("foo"));
    }

    public function testGetExtractionKey()
    {
        $this->assertEquals(
            "foo",
            $this->makeParameter("foo")->getExtractionKey()
        );
    }
}
