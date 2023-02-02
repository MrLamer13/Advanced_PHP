<?php

namespace GeekBrains\LevelTwo\UnitTests\Person;

use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Person\Person;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    public function testItReturnsUuid(): void
    {
        $person = new Person(
            new UUID('123e4567-e89b-12d3-a456-426614174006'),
            new Name('Вася', 'Пупкин'),
            new \DateTimeImmutable('now')
        );
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174006', (string)$person->uuid());
    }

    public function testItReturnsName(): void
    {
        $person = new Person(
            new UUID('123e4567-e89b-12d3-a456-426614174006'),
            new Name('Вася', 'Пупкин'),
            new \DateTimeImmutable('now')
        );
        $this->assertEquals('Вася Пупкин', (string)$person->name());
    }

    public function testItReturnsString(): void
    {
        $person = new Person(
            new UUID('123e4567-e89b-12d3-a456-426614174006'),
            new Name('Вася', 'Пупкин'),
            new \DateTimeImmutable('2023-01-31')
        );
        $this->assertEquals(
            'Вася Пупкин (на сайте с 2023-01-31)',
            (string)$person);
    }

}