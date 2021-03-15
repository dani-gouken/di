<?php


namespace Atom\DI\Contracts;

interface ExtractionParameterContract
{
    public function getExtractionKey(): string;

    public function getObjectMapping(): ?MappingContract;

    public function getParameterMapping(): ?MappingContract;
}
