<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\UUID;
use PHPUnit\Framework\TestCase;

class UUIDTest extends TestCase
{
    public function testItThrowsAnExceptionWhenArgumentIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Неправильный UUID: 123');
        $uuid = new UUID('123');
    }
}