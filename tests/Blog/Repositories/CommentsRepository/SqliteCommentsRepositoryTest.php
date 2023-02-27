<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\UnitTests\DummyLogger;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteCommentsRepositoryTest extends TestCase
{
    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());

        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage('Комментарий не найден: 123e4567-e89b-12d3-a456-426614174002');

        $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174002'));
    }

    public function testItSavesCommentToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'uuid' => '123e4567-e89b-12d3-a456-426614174002',
                'post_uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'author_uuid' => '123e4567-e89b-12d3-a456-426614174001',
                'text' => 'Какой-то комментарий'
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);


        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174001'),
            'ivan123',
            '123',
            new Name('Ivan', 'Nikitin')
        );

        $post = new Post(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            $user,
            'Заголовок',
            'Какой-то текст'
        );

        $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());
        $repository->save(
            new Comment(
                new UUID('123e4567-e89b-12d3-a456-426614174002'),
                $post,
                $user,
                'Какой-то комментарий'
            )
        );
    }

    public function testItGetsCommentFromDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '123e4567-e89b-12d3-a456-426614174002',
            'post_uuid' => '123e4567-e89b-12d3-a456-426614174001',
            'author_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'username' => 'ivan123',
            'password' => '123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'title' => 'Зоголовок',
            'text' => 'Какой-то комментарий'
        ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());
        $comment = $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174002'));

        $this->assertSame('123e4567-e89b-12d3-a456-426614174002', (string)$comment->uuid());
    }

}