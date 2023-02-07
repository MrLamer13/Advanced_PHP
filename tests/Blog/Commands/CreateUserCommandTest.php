<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Commands;

use GeekBrains\LevelTwo\Blog\Commands\Arguments;
use GeekBrains\LevelTwo\Blog\Commands\CreateUserCommand;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\ArgumentsException;
use GeekBrains\LevelTwo\Exceptions\CommandException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
// Проверяем, что команда создания пользователя бросает исключение,
// если пользователь с таким именем уже существует
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $usersRepository = new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
            }
            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByUsername(string $username): User
            {
                return new User(UUID::random(), "user123", new Name("first", "last"));
            }
        };
// Создаём объект команды
// У команды одна зависимость - UsersRepositoryInterface
        $command = new CreateUserCommand($usersRepository);
// Описываем тип ожидаемого исключения
        $this->expectException(CommandException::class);
        // и его сообщение
        $this->expectExceptionMessage('Пользователь уже существует: Ivan');
// Запускаем команду с аргументами
        $command->handle(new Arguments(['username' => 'Ivan']));
    }


// Тест проверяет, что команда действительно требует имя пользователя

    public function testItRequiresFirstName(): void
    {
        $command = new CreateUserCommand($this->makeUsersRepository());
// Ожидаем, что будет брошено исключение
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('Нет такого аргумента: first_name');
// Запускаем команду
        $command->handle(new Arguments(['username' => 'Ivan']));
    }

    // Тест проверяет, что команда действительно требует фамилию пользователя

    public function testItRequiresLastName(): void
    {
// Передаём в конструктор команды объект, возвращаемый нашей функцией
        $command = new CreateUserCommand($this->makeUsersRepository());
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('Нет такого аргумента: last_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',
// Нам нужно передать имя пользователя,
// чтобы дойти до проверки наличия фамилии
            'first_name' => 'Ivan',
        ]));
    }


    // Тест, проверяющий, что команда сохраняет пользователя в репозитории

    public function testItSavesUserToRepository(): void
    {
        $usersRepository = $this->makeUsersRepository();
// Передаём наш мок в команду
        $command = new CreateUserCommand($usersRepository);
// Запускаем команду
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]));
// Проверяем утверждение относительно мока,
// а не утверждение относительно команды
        $this->assertTrue($usersRepository->wasCalled());
    }


    // Функция возвращает объект типа UsersRepositoryInterface
    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {
            private bool $called = false;
            public function save(User $user): void
            {
                $this->called = true;
            }
            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
    }



}