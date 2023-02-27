<?php

namespace GeekBrains\LevelTwo\Person;

use DateTimeImmutable;
use GeekBrains\LevelTwo\Blog\UUID;

class Person
{
    public function __construct(
        private UUID              $uuid,
        private Name              $name,
        private DateTimeImmutable $registeredOn
    )
    {
    }

    public function uuid(): UUID
    {
        return $this->uuid;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name .
            ' (на сайте с ' . $this->registeredOn->format('Y-m-d') . ')';
    }
}