<?php


namespace Atom\DI\Contracts;

interface MappingContract
{
    /**
     * Array filled with the keys of all mapped entities
     * @return array<string>
     */
    public function getMappedEntities(): array;

    /**
     * @param string $key
     * @return DefinitionContract
     */
    public function getMappingFor(string $key): DefinitionContract;

    /**
     * @param string $key
     * @return bool
     */
    public function hasMappingFor(string $key): bool;

    /**
     * @param string $key
     * @param DefinitionContract $definition
     */
    public function add(string $key, DefinitionContract $definition);
}
