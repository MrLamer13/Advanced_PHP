<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository;

use GeekBrains\LevelTwo\Blog\PostLike;
use GeekBrains\LevelTwo\Blog\UUID;

interface PostsLikesRepositoryInterface
{
    public function save(PostLike $postLike): void;
    public function getByPostUuid(UUID $postUuid): array;
}