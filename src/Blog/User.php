<?php

namespace GeekBrains\LevelTwo\Blog;

use GeekBrains\LevelTwo\Person\Name;

class User
{
    public function __construct(
        private UUID   $uuid,
        private string $username,
        private string $hashedPassword,
        private Name   $name
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

    public function username(): string
    {
        return $this->username;
    }

    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    // Функция для вычисления хеша
    private static function hash(string $password, UUID $uuid): string
    {
        return hash('sha256', $uuid . $password);
    }

    // Функция для проверки предъявленного пароля
    public function checkPassword(string $password): bool
    {
        return $this->hashedPassword === self::hash($password, $this->uuid);
    }

    // Функция для создания нового пользователя
    public static function createFrom(
        string $username,
        string $password,
        Name   $name
    ): self
    {
        $uuid = UUID::random();
        return new self(
            $uuid,
            $username,
            self::hash($password, $uuid),
            $name
        );
    }

}