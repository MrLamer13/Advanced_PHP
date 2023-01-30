<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct (
        private \PDO $connection
    )
    {}
    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (uuid, username, first_name, last_name)
                    VALUES (:uuid, :username, :first_name, :last_name)'
        );

        $statement->execute([
            'uuid' => (string)$user->uuid(),
            'username' => $user->username(),
            'first_name' => $user->name()->first(),
            'last_name' => $user->name()->last(),
        ]);
    }

    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE uuid = :uuid'
        );
        $statement->execute([
            'uuid' => (string)$uuid,
        ]);

        return $this->getUser($statement, $uuid);
    }

    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );
        $statement->execute([
            'username' => $username,
        ]);
        return $this->getUser($statement, $username);
    }

    private function getUser(\PDOStatement $statement, string $userNameOrUuid): User
    {
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new UserNotFoundException(
                "Пользователь не найден: $userNameOrUuid"
            );
        }

        return new User(
            new UUID($result['uuid']),
            $result['username'],
            new Name($result['first_name'], $result['last_name'])
        );
    }
}