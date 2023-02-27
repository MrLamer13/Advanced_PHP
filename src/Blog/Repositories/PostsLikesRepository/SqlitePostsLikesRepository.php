<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository;

use GeekBrains\LevelTwo\Blog\PostLike;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostLikesNotFoundException;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqlitePostsLikesRepository implements PostsLikesRepositoryInterface
{
    public function __construct(
        private PDO             $connection,
        private LoggerInterface $logger
    )
    {
    }

    public function save(PostLike $postLike): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts_likes (uuid, post_uuid, author_uuid) 
                    VALUES (:uuid, :post_uuid, :author_uuid)'
        );

        $postLikeUuid = (string)$postLike->uuid();

        $statement->execute([
            'uuid' => $postLikeUuid,
            'post_uuid' => (string)$postLike->post()->uuid(),
            'author_uuid' => (string)$postLike->author()->uuid()
        ]);

        $this->logger->info("Создан лайк поста: $postLikeUuid");
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

    private function getPostLikes(PDOStatement $statement, string $postUuid): array
    {
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($result === []) {
            $this->logger->warning("Лайки поста не найдены: $postUuid");
            throw new PostLikesNotFoundException(
                "Лайки поста не найдены: $postUuid"
            );
        }

        $postRepository = new SqlitePostsRepository($this->connection, $this->logger);
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $postLikes = [];

        foreach ($result as $postLike) {
            $post = $postRepository->get(new UUID($postLike['post_uuid']));
            $user = $userRepository->get(new UUID($postLike['author_uuid']));

            $postLikes[] = new PostLike(
                new UUID($postLike['uuid']),
                $post,
                $user
            );
        }

        $this->logger->info("Получены лайки поста: $postUuid");

        return $postLikes;
    }
}