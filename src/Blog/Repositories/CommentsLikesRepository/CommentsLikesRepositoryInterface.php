<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository;

use GeekBrains\LevelTwo\Blog\CommentLike;
use GeekBrains\LevelTwo\Blog\UUID;

interface CommentsLikesRepositoryInterface
{
    public function save(CommentLike $commentLike): void;
    public function getByCommentUuid(UUID $commentUuid): array;

}