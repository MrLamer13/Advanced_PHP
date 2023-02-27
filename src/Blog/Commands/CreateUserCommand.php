<?php

namespace GeekBrains\LevelTwo\Blog\Commands;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use Psr\Log\LoggerInterface;

class CreateUserCommand
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface          $logger,
    )
    {
    }

    public function handle(Arguments $arguments): void
    {
        $this->logger->info("Команда создания пользователя запущена");

        $username = $arguments->get('username');

        if ($this->userExists($username)) {
            $this->logger->warning("Пользователь уже существует: $username");
            return;
        }

        // Создаём объект пользователя. Функция createFrom сама создаст UUID и захеширует пароль
        $user = User::createFrom(
            $username,
            $arguments->get('password'),
            new Name(
                $arguments->get('first_name'),
                $arguments->get('last_name')
            )
        );
        $this->usersRepository->save($user);

        $this->logger->info('Пользователь создан: ' . $user->uuid());
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