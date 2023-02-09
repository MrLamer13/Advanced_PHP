<?php

namespace GeekBrains\LevelTwo\UnitTests\Container;

class ClassDependingOnAnother
{
    public function __construct(
        private SomeClassWithoutDependencies $one,
        private SomeClassWithParameter $two,
    )
    {}
}