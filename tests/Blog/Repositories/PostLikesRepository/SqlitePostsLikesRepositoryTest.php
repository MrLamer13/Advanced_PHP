<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Repositories\PostLikesRepository;

use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\PostLike;
use GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository\SqlitePostsLikesRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostLikesNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\UnitTests\DummyLogger;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqlitePostsLikesRepositoryTest extends TestCase
{
    public function testItTrowsAnExceptionsWhenPostLikesNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetchAll')->willReturn([]);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostsLikesRepository($connectionStub, new DummyLogger());

        $this->expectException(PostLikesNotFoundException::class);
        $this->expectExceptionMessage('Лайки поста не найдены: 123e4567-e89b-12d3-a456-426614174000');

        $repository->getByPostUuid(new UUID('123e4567-e89b-12d3-a456-426614174000'));
    }

    public function testItSavesPostsLikeToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'post_uuid' => '123e4567-e89b-12d3-a456-426614174002',
                'author_uuid' => '123e4567-e89b-12d3-a456-426614174001',
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $author = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174001'),
            'ivan123',
            '123',
            new Name('Ivan', 'Nikitin')
        );
        $post = new Post(
            new UUID('123e4567-e89b-12d3-a456-426614174002'),
            $author,
            'Заголовок',
            'Какой-то текст'
        );

        $repository = new SqlitePostsLikesRepository($connectionStub, new DummyLogger());
        $repository->save(
            new PostLike(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $post,
                $author
            )
        );
    }

    public function testItGetsPostsLikeFromDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '123e4567-e89b-12d3-a456-426614174002',
            'author_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'username' => 'ivan123',
            'password' => '123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'title' => 'Заголовок',
            'text' => 'Какой-то текст'
        ]);
        $statementMock->method('fetchAll')->willReturn([[
            'uuid' => '123e4567-e89b-12d3-a456-426614174001',
            'post_uuid' => '123e4567-e89b-12d3-a456-426614174002',
            'author_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'username' => 'ivan123',
            'password' => '123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'title' => 'Заголовок',
            'text' => 'Какой-то текст'
        ]]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqlitePostsLikesRepository($connectionStub, new DummyLogger());
        $likes = $repository->getByPostUuid(new UUID('123e4567-e89b-12d3-a456-426614174002'));

        $this->assertSame('123e4567-e89b-12d3-a456-426614174001', (string)$likes[0]->uuid());
    }
}