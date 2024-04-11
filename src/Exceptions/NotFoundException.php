<?php
namespace Atom\DI\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct(public readonly string $key)
    {
        $message = "The container is unable to resolve [$key].";
        parent::__construct($message);
    }
}
