<?php
namespace Atom\DI\Tests;

use Atom\DI\Container;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{

    public function getContainer()
    {
        return new Container();
    }
}
