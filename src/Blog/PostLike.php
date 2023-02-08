<?php

namespace GeekBrains\LevelTwo\Blog;

class PostLike
{
    public function __construct(
        private UUID $uuid,
        private Post $post,
        private User $author
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


}