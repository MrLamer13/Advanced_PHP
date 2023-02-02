<?php

namespace GeekBrains\LevelTwo\Blog;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;

class UUID extends \GeekBrains\LevelTwo\Blog\Comment
{
    public function __construct(
        private string $uuidString
    ) {
        if (!uuid_is_valid($uuidString)) {
            throw new InvalidArgumentException(
                "Неправильный UUID: $this->uuidString"
            );
        }
    }

// Генерируем новый случайный UUID и получаем его в качестве объекта нашего класса
    public static function random(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }
    public function __toString(): string
    {
        return $this->uuidString;
    }
}