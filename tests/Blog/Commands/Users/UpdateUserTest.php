<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Commands\Users;

use GeekBrains\LevelTwo\Blog\Commands\Users\UpdateUser;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class UpdateUserTest extends TestCase
{
    public function testItUpdateUserToRepository(): void
    {
        $usersRepository = $this->makeUsersRepository();
        $command = new UpdateUser(
            $usersRepository
        );

        $command->run(
            new ArrayInput([
                'uuid' => '9c4ec61b-747f-45ef-b66a-28bc61d0fe0d',
                '-f' => 'Petr',
                '-l' => 'Petrov'
            ]),
            new NullOutput()
        );

        $this->assertTrue($usersRepository->wasCalled());
    }

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
                return new User(
                    new UUID('9c4ec61b-747f-45ef-b66a-28bc61d0fe0d'),
                    'Ivan',
                    '123',
                    new Name(
                        'Ivan',
                        'Pupkin'
                    )
                );
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException('Не найдено');
            }

            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
    }
}