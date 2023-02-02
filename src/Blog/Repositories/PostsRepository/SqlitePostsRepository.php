<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    public function __construct (
        private \PDO $connection
    )
    {}
    public function save(Post $post): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, author_uuid, title, text)
                    VALUES (:uuid, :author_uuid, :title, :text)'
        );

        $statement->execute([
            'uuid' => (string)$post->uuid(),
            'author_uuid' => $post->author()->uuid(),
            'title' => $post->title(),
            'text' => $post->text(),
        ]);
    }

    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = :uuid'
        );
        $statement->execute([
            'uuid' => (string)$uuid,
        ]);

        return $this->getPost($statement, $uuid);
    }

    private function getPost(\PDOStatement $statement, string $uuid): Post
    {
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new PostNotFoundException(
                "Пост не найден: $uuid"
            );
        }

        $userRepository = new SqliteUsersRepository($this->connection);
        $user = $userRepository->get(new UUID($result['author_uuid']));

        return new Post(
            new UUID($result['uuid']),
            $user,
            $result['title'],
            $result['text']
        );
    }

}