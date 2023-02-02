<?php

namespace GeekBrains\LevelTwo\Blog;

class Post
{
    public function __construct(
        private UUID $uuid,
        private User $author,
        private string $title,
        private string $text
    ) {
    }

    public function uuid(): UUID
    {
        return $this->uuid;
    }

    public function author(): User
    {
        return $this->author;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function text(): string
    {
        return $this->text;
    }

}