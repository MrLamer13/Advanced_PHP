<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\InMemoryUsersRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class InMemoryUsersRepositoryTest extends TestCase
{
    public function testItThrowsAnExceptionWhenUserNotFoundBuUuid():void
    {
        $repository = new InMemoryUsersRepository();

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Пользователь не найден: 123e4567-e89b-12d3-a456-426614174006');

        $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174006'));
    }

    public function testItThrowsAnExceptionWhenUserNotFoundBuUsername():void
    {
        $repository = new InMemoryUsersRepository();

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Пользователь не найден: Ivan');

        $repository->getByUsername('Ivan');
    }

    public function testItReturnUserByUuid(): void
    {
        $repository = new InMemoryUsersRepository();
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'ivan123',
            new Name('Ivan', 'Nikitin')
        );
        $repository->save($user);

        $this->assertEquals(
            $user,
            $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'))
        );


    }

    public function testItReturnUserByUsername(): void
    {
        $repository = new InMemoryUsersRepository();
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'ivan123',
            new Name('Ivan', 'Nikitin')
        );
        $repository->save($user);

        $this->assertEquals(
            $user,
            $repository->getByUsername('ivan123')
        );


    }

}