<?php

namespace GeekBrains\LevelTwo\Blog;

class CommentLike
{
    public function __construct(
        private UUID $uuid,
        private Comment $comment,
        private User $author
    )
    {    }

    public function uuid(): UUID
    {
        return $this->uuid;
    }

    public function comment(): Comment
    {
        return $this->comment;
    }

    public function author(): User
    {
        return $this->author;
    }
}