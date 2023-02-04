<?php

namespace GeekBrains\LevelTwo\Blog;

use GeekBrains\LevelTwo\Person\Name;

class User
{
    public function __construct(
        private UUID $uuid,
        private string $username,
        private Name $name
    )
    {}

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

}