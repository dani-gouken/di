<?php

namespace Atom\DI\Tests\Misc\CircularDependency;

class CDDummy0
{
    public function __construct(CDDummy1 $dm)
    {
    }
}
