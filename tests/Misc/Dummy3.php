<?php

namespace Atom\DI\Tests\Misc;

class Dummy3
{
    public function __invoke()
    {
        return "foo";
    }

    public function getBar(string $bar):string
    {
        return $bar;
    }
}
