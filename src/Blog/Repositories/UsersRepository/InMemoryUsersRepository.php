<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use Psr\Log\LoggerInterface;

class InMemoryUsersRepository implements UsersRepositoryInterface
{
    public function __construct(
        private LoggerInterface $logger
    )
    {    }

    private array $users = [];
    public function save(User $user): void
    {
        $this->users[] = $user;

        $userUuid = $user->uuid();

        $this->logger->info("Создан пользователь: $userUuid");
    }

    public function get(UUID $uuid): User
    {
        foreach ($this->users as $user) {
            if ((string)$user->uuid() === (string)$uuid) {
                $this->logger->info("Получен пользователь: $uuid");
                return $user;
            }
        }
        $this->logger->warning("Пользователь не найден: $uuid");
        throw new UserNotFoundException("Пользователь не найден: $uuid");
    }

    public function getByUsername(string $username): User
    {
        foreach ($this->users as $user) {
            if ($user->username() === $username) {
                $this->logger->info("Получен пользователь: $username");
                return $user;
            }
        }
        $this->logger->warning("Пользователь не найден: $username");
        throw new UserNotFoundException("Пользователь не найден: $username");
    }
}