<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;

class InMemoryUsersRepository implements UsersRepositoryInterface
{
    private array $users = [];
    public function save(User $user): void
    {
        $this->users[] = $user;
    }

    public function get(UUID $uuid): User
    {
        foreach ($this->users as $user) {
            if ((string)$user->uuid() === (string)$uuid) {
                return $user;
            }
        }
        throw new UserNotFoundException("Пользователь не найден: $uuid");
    }

    public function getByUsername(string $username): User
    {
        foreach ($this->users as $user) {
            if ($user->username() === $username) {
                return $user;
            }
        }
        throw new UserNotFoundException("Пользователь не найден: $username");
    }
}