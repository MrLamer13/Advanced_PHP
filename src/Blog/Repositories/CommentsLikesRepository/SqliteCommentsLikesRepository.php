<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository;

use GeekBrains\LevelTwo\Blog\CommentLike;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentLikesNotFoundException;
use Psr\Log\LoggerInterface;

class SqliteCommentsLikesRepository implements CommentsLikesRepositoryInterface
{
    public function __construct(
        private \PDO $connection,
        private LoggerInterface $logger
    )
    {    }

    public function save(CommentLike $commentLike): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments_likes (uuid, comment_uuid, author_uuid)
                    VALUES (:uuid, :comment_uuid, :author_uuid)'
        );

        $commentLikeUuid = (string)$commentLike->uuid();

        $statement->execute([
            'uuid' => $commentLikeUuid,
            'comment_uuid' => (string)$commentLike->comment()->uuid(),
            'author_uuid' => (string)$commentLike->author()->uuid()
        ]);

        $this->logger->info("Создан лайк комментария: $commentLikeUuid");
    }

    public function getByCommentUuid(UUID $commentUuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments_likes WHERE comment_uuid = :comment_uuid'
        );
        $statement->execute([
            'comment_uuid' => (string)$commentUuid
        ]);

        return $this->getCommentLikes($statement, $commentUuid);
    }

    private function getCommentLikes(\PDOStatement $statement, string $commentUuid): array
    {
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->logger->warning("Лайки комментария не найдены: $commentUuid");
            throw new CommentLikesNotFoundException(
                "Лайки комментария не найдены: $commentUuid"
            );
        }

        $commentRepository = new SqliteCommentsRepository($this->connection, $this->logger);
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $commentLikes = [];

        foreach ($result as $commentLike) {
            $comment = $commentRepository->get(new UUID($commentLike['comment_uuid']));
            $user = $userRepository->get(new UUID($commentLike['author_uuid']));

            $commentLikes[] = new CommentLike(
                new UUID($commentLike['uuid']),
                $comment,
                $user
            );
        }

        $this->logger->info("Получены лайки комментария: $commentUuid");

        return $commentLikes;
    }
}