<?php

namespace GeekBrains\LevelTwo\UnitTests\Person;

use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testItReturnString():void
    {
        $name = new Name('Вася', 'Пупкин');
        $this->assertSame('Вася Пупкин', (string)$name);
    }
}