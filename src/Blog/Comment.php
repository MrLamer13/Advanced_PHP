<?php

namespace GeekBrains\LevelTwo\Blog;

class Comment
{

    public function __construct(
        private UUID $uuid,
        private Post $post,
        private User $author,
        private string $text
    )
    {}

    public function uuid(): UUID
    {
        return $this->uuid;
    }

    public function post(): Post
    {
        return $this->post;
    }

    public function author(): User
    {
        return $this->author;
    }

    public function text(): string
    {
        return $this->text;
    }

}