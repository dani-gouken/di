<?php


namespace Atom\DI\Storage;

use Atom\DI\Definitions\BuildObject;
use Atom\DI\Definitions\Value;
use Atom\DI\DIC;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;

class SingletonStorage extends AbstractStorage
{
    use ClassBindingTrait;
    /**
     * @var DIC
     */
    protected $container;

    /**
     * Resolved bindings
     * @var array
     */
    public $resolvedBindings = [];
    public const STORAGE_KEY = "SINGLETONS";

    public function __construct(DIC $container)
    {
        parent::__construct($container);
        $this->container = $container;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->resolvedBindings)) {
            return $this->resolvedBindings[$key];
        }
        if (!$this->has($key)) {
            throw new NotFoundException($key, $this);
        }
        $value = $this->container->extract($this->resolve($key), $key);
        $this->resolvedBindings[$key] = $value;
        return $value;
    }

    /**
     * @return string
     */
    public function getStorageKey(): string
    {
        return self::STORAGE_KEY;
    }
}
