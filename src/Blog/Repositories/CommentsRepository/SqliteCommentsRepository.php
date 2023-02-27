<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentNotFoundException;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    public function __construct(
        private PDO             $connection,
        private LoggerInterface $logger
    )
    {
    }

    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, post_uuid, author_uuid, text)
                    VALUES (:uuid, :post_uuid, :author_uuid, :text)'
        );

        $commentUuid = (string)$comment->uuid();

        $statement->execute([
            'uuid' => $commentUuid,
            'post_uuid' => $comment->post()->uuid(),
            'author_uuid' => $comment->author()->uuid(),
            'text' => $comment->text(),
        ]);

        $this->logger->info("Создан комментарий: $commentUuid");
    }

    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = :uuid'
        );
        $statement->execute([
            'uuid' => (string)$uuid,
        ]);

        return $this->getComment($statement, $uuid);
    }

    private function getComment(PDOStatement $statement, string $uuid): Comment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->logger->warning("Комментарий не найден: $uuid");
            throw new CommentNotFoundException(
                "Комментарий не найден: $uuid"
            );
        }

        $postRepository = new SqlitePostsRepository($this->connection, $this->logger);
        $post = $postRepository->get(new UUID($result['post_uuid']));
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
        $user = $userRepository->get(new UUID($result['author_uuid']));

        $this->logger->info("Получен комментарий: $uuid");

        return new Comment(
            new UUID($result['uuid']),
            $post,
            $user,
            $result['text']
        );
    }
}