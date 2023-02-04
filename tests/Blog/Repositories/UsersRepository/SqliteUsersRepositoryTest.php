<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class SqliteUsersRepositoryTest extends TestCase
{

    // Тест, проверяющий, что SQLite-репозиторий бросает исключение,
    // когда запрашиваемый пользователь не найден
    public function testItThrowsAnExceptionWhenUserNotFound(): void
    {
        $connectionStub = $this->createStub(\PDO::class);
        $statementStub = $this->createStub(\PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteUsersRepository($connectionStub);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Пользователь не найден: Ivan');

        $repository->getByUsername('Ivan');
    }

    // Тест, проверяющий, что репозиторий сохраняет данные в БД
    public function testItSavesUserToDatabase(): void
    {
// 2. Создаём стаб подключения
        $connectionStub = $this->createStub(\PDO::class);
// 4. Создаём мок запроса, возвращаемый стабом подключения
        $statementMock = $this->createMock(\PDOStatement::class);
// 5. Описываем ожидаемое взаимодействие нашего репозитория с моком запроса
        $statementMock
            ->expects($this->once()) // Ожидаем, что будет вызван один раз
            ->method('execute') // метод execute
            ->with([ // с единственным аргументом - массивом
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'username' => 'ivan123',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]);
// 3. При вызове метода prepare стаб подключения возвращает мок запроса
        $connectionStub->method('prepare')->willReturn($statementMock);
// 1. Передаём в репозиторий стаб подключения
        $repository = new SqliteUsersRepository($connectionStub);
// Вызываем метод сохранения пользователя
        $repository->save(
            new User( // Свойства пользователя точно такие, как и в описании мока
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                'ivan123',
                new Name('Ivan', 'Nikitin')
            )
        );
    }

    public function testItGetsUserFromDatabaseByUuid(): void
    {
        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'username' => 'ivan123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin'
        ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteUsersRepository($connectionStub);
        $user = $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $this->assertSame('123e4567-e89b-12d3-a456-426614174000', (string)$user->uuid());
    }

    public function testItGetsUserFromDatabaseByUsername(): void
    {
        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'username' => 'ivan123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin'
        ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteUsersRepository($connectionStub);
        $user = $repository->getByUsername('ivan123');

        $this->assertSame('ivan123', $user->username());
    }
}