<?php


namespace Atom\DI\Contracts;

use Atom\DI\Container;

interface ExtractorContract
{
    public function extract(?ExtractionParameterContract $params, Container $container): mixed;

    public function isValidExtractionParameter(?ExtractionParameterContract $params): bool;
}
