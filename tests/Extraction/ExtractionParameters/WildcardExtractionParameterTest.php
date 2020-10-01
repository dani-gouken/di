<?php


namespace Atom\DI\Tests\Extraction\ExtractionParameters;

use Atom\DI\Extraction\ExtractionParameters\WildcardExtractionParameter;
use Atom\DI\Tests\BaseTestCase;

class WildcardExtractionParameterTest extends BaseTestCase
{
    public function testGetters()
    {
        $parameter = new WildcardExtractionParameter("foo", "bar", "baz");
        $this->assertEquals("foo", $parameter->getClassName());
        $this->assertEquals("bar", $parameter->getReplacement());
        $this->assertEquals("baz", $parameter->getPattern());
        $this->assertEquals("foo", $parameter->getExtractionKey());
    }
}
