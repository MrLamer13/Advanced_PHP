<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct(
        private PDO             $connection,
        private LoggerInterface $logger
    )
    {
    }

    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (uuid, username, password, first_name, last_name)
                    VALUES (:uuid, :username, :password, :first_name, :last_name)'
        );

        $userUuid = (string)$user->uuid();

        $statement->execute([
            'uuid' => $userUuid,
            'username' => $user->username(),
            'password' => $user->hashedPassword(),
            'first_name' => $user->name()->first(),
            'last_name' => $user->name()->last()
        ]);

        $this->logger->info("Создан пользователь: $userUuid");
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

    private function getUser(PDOStatement $statement, string $userNameOrUuid): User
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            $this->logger->warning("Пользователь не найден: $userNameOrUuid");
            throw new UserNotFoundException(
                "Пользователь не найден: $userNameOrUuid"
            );
        }

        $this->logger->info("Получен пользователь: $userNameOrUuid");
        return new User(
            new UUID($result['uuid']),
            $result['username'],
            $result['password'],
            new Name($result['first_name'], $result['last_name'])
        );
    }
}