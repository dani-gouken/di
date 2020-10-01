<?php

namespace Atom\DI\Tests\Extraction;

use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\DI\Exceptions\UnsupportedInvokerException;
use Atom\DI\Extraction\ExtractionParameters\ValueExtractionParameter;
use Atom\DI\Extraction\ExtractionParameters\WildcardExtractionParameter;
use Atom\DI\Extraction\WildcardExtractor;
use Atom\DI\Tests\BaseTestCase;
use Atom\DI\Tests\Misc\Dummy1;

class WildcardExtractorTest extends BaseTestCase
{
    public function testIsValidExtractionParameter()
    {
        $extractor = new WildcardExtractor();
        $this->assertTrue($extractor->isValidExtractionParameter(
            new WildcardExtractionParameter("foo", "bar", "bae")
        ));
        $this->assertFalse($extractor->isValidExtractionParameter(new ValueExtractionParameter("bar")));
    }

    /**
     * @throws ContainerException
     * @throws StorageNotFoundException
     * @throws UnsupportedInvokerException
     */
    public function testExtract()
    {
        $container = $this->getContainer();
        $container->wildcards()->store(
            $pattern = "Atom\DI\Tests\Fixtures\*",
            $container->as()->wildcardFor($replacement = "Atom\DI\Tests\Misc\*")
        );
        $extractor = $container->getExtractor(WildcardExtractor::class);
        $this->assertInstanceOf(
            Dummy1::class,
            $extractor->extract(
                new WildcardExtractionParameter(
                    'Atom\DI\Tests\Misc\Dummy1',
                    $pattern,
                    $replacement
                ),
                $container
            )
        );
    }
}
