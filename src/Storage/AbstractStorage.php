<?php


namespace Atom\DI\Storage;

use Atom\DI\Contracts\DefinitionContract;
use Atom\DI\Contracts\StorageContract;
use Atom\DI\DIC;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Extraction\FunctionExtractor;
use Atom\DI\Extraction\MethodExtractor;
use Atom\DI\Extraction\ObjectExtractor;
use Atom\DI\Extraction\ValueExtractor;
use InvalidArgumentException;

/**
 * Class AbstractArrayStorage
 * @property DIC $container
 * @package Atom\DI\Storage
 */
abstract class AbstractStorage implements StorageContract
{
    protected $supportedExtractors = [
        ValueExtractor::class,
        ObjectExtractor::class,
        MethodExtractor::class,
        FunctionExtractor::class
    ];
    protected $container;

    protected $definitionIndex = 0;
    /**
     * @var array<DefinitionContract>
     */
    protected $definitions = [];

    protected $bindings = [];

    public function __construct(DIC $dic)
    {
        $this->container = $dic;
    }

    public function getContainer(): DIC
    {
        return $this->container;
    }

    /**
     * @param string $extractorClassName
     * @throws ContainerException
     */
    public function addSupportForExtractor(string $extractorClassName): void
    {
        if (!$this->container->hasExtractor($extractorClassName)) {
            throw new ContainerException("You are trying add the support for the invoker [$extractorClassName] 
            in the storage [" . self::class . "], but that invoker is not registered in the container");
        }
        if (!array_key_exists($extractorClassName, $this->supportedExtractors)) {
            $this->supportedExtractors[] = $extractorClassName;
        }
    }

    public function supportExtractor(string $extractorClassName): bool
    {
        return in_array($extractorClassName, $this->supportedExtractors);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->bindings);
    }

    public function contains(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * @param $key
     * @param DefinitionContract $value
     */
    public function store($key, DefinitionContract $value)
    {
        if (!$this->isValidKey($key)) {
            throw new InvalidArgumentException('The key needs to be either a string or an array');
        }
        $index = $this->definitionIndex;
        $this->definitions[$this->definitionIndex] = $value;
        $bindings = is_array($key) ? $key : [$key];
        foreach ($bindings as $binding) {
            $this->bindings[$binding] = $index;
        }
        $this->definitionIndex++;
    }


    /**
     * @param string $key
     * @return mixed
     * @throws NotFoundException
     */
    public function resolve(string $key): DefinitionContract
    {
        if (!$this->has($key)) {
            throw new NotFoundException($key, $this);
        }
        return $this->definitions[$this->bindings[$key]];
    }

    /**com
     * @param string $key
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get(string $key)
    {
        return $this->container->extract($this->resolve($key), $key);
    }

    /**
     * @param string $key
     * @param callable $extendFunction
     * @return mixed|void
     * @throws NotFoundException
     */
    public function extends(string $key, callable $extendFunction)
    {
        $this->store($key, $extendFunction($this->resolve($key)));
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function remove(string $key)
    {
        if ($this->has($key)) {
            unset($this->bindings[$key]);
        }
    }

    protected function isValidKey($key): bool
    {
        return is_string($key) || is_array($key);
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
