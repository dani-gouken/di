<?php

namespace Atom\DI\Tests\Misc\CircularDependency;

class CDDummy1
{
    public function __construct(CDDummy0 $dm)
    {
    }
}
