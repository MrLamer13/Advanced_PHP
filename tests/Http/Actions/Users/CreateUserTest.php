<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\Users;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommandException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\Users\CreateUser;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $usersRepository = $this->usersRepository([]);

        $request = new Request([], [],
            '{"username": "vasia", "password": "123", "first_name": "Vasia", "last_name": "Pupkin"}');

        $action = new CreateUser($usersRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfRequestNotContainAllData(): void
    {
        $usersRepository = $this->usersRepository([]);

        $request = new Request([], [],
            '{"username": "vasia", "password": "123", "first_name": "Vasia"}');

        $action = new CreateUser($usersRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Нет такого поля: last_name"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfUserExist(): void
    {
        $usersRepository = $this->usersRepository([
            new User(
                UUID::random(),
                'vasia',
                '123',
                new Name('Vasia',
                    'Pupkin'
                )
            )
        ]);

        $request = new Request([], [],
            '{"username": "vasia", "password": "123", "first_name": "Vasia", "last_name": "Pupkin"}');

        $action = new CreateUser($usersRepository);

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("Пользователь уже существует: vasia");
        $response = $action->handle($request);

    }


    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
                $this->users[] = $user;
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid == $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Не найдено");
            }

            public function getByUsername(string $username): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $username === $user->username()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Не найдено");
            }
        };
    }
}