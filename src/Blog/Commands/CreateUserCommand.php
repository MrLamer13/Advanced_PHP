<?php

namespace GeekBrains\LevelTwo\Blog\Commands;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use Psr\Log\LoggerInterface;

class CreateUserCommand
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger,
    )
    {}

    public function handle(Arguments $arguments): void
    {
        $this->logger->info("Команда создания пользователя запущена");

        $username = $arguments->get('username');

        if ($this->userExists($username)) {
            $this->logger->warning("Пользователь уже существует: $username");
            return;
        }

        $uuid = UUID::random();

        $this->usersRepository->save(new User(
            $uuid,
            $username,
            new Name($arguments->get('first_name'), $arguments->get('last_name'))
        ));

        $this->logger->info("Пользователь создан: $uuid");
    }

    private function userExists(string $username): bool
    {
        try {
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }

}