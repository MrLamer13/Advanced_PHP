<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository;

use GeekBrains\LevelTwo\Blog\PostLike;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostLikesNotFoundException;

class SqlitePostsLikesRepository implements PostsLikesRepositoryInterface
{
    public function __construct(
        private \PDO $connection
    )
    {    }

    public function save(PostLike $postLike): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts_likes (uuid, post_uuid, author_uuid) 
                    VALUES (:uuid, :post_uuid, :author_uuid)'
        );

        $statement->execute([
            'uuid' => (string)$postLike->uuid(),
            'post_uuid' => (string)$postLike->post()->uuid(),
            'author_uuid' => (string)$postLike->author()->uuid()
        ]);
    }

    public function getByPostUuid(UUID $postUuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts_likes WHERE post_uuid = :post_uuid'
        );
        $statement->execute([
            'post_uuid' => (string)$postUuid
        ]);

        return $this->getPostLikes($statement, $postUuid);
    }

    private function getPostLikes(\PDOStatement $statement, string $postUuid): array
    {
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new PostLikesNotFoundException(
                "Лайки поста не найдены: $postUuid"
            );
        }

        $postRepository = new SqlitePostsRepository($this->connection);
        $userRepository = new SqliteUsersRepository($this->connection);
        $postLikes = [];

        foreach ($result as $postlike) {
            $post = $postRepository->get(new UUID($postlike['post_uuid']));
            $user = $userRepository->get(new UUID($postlike['author_uuid']));

            $postLikes[] = new PostLike(
                new UUID($postlike['uuid']),
                $post,
                $user
            );
        }

        return $postLikes;
    }
}