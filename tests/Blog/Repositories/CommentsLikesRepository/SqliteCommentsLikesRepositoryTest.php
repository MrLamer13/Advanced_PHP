<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Repositories\CommentsLikesRepository;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\CommentLike;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository\SqliteCommentsLikesRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentLikesNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\UnitTests\DummyLogger;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteCommentsLikesRepositoryTest extends TestCase
{
    public function testItSavesCommentsLikeToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'comment_uuid' => '123e4567-e89b-12d3-a456-426614174003',
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
        $comment = new Comment(
            new UUID('123e4567-e89b-12d3-a456-426614174003'),
            $post,
            $author,
            'Комментарий'
        );

        $repository = new SqliteCommentsLikesRepository($connectionStub, new DummyLogger());
        $repository->save(
            new CommentLike(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $comment,
                $author
            )
        );
    }

    public function testItGetsCommentsLikeFromDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '123e4567-e89b-12d3-a456-426614174003',
            'post_uuid' => '123e4567-e89b-12d3-a456-426614174002',
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
            'comment_uuid' => '123e4567-e89b-12d3-a456-426614174003',
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

        $repository = new SqliteCommentsLikesRepository($connectionStub, new DummyLogger());
        $likes = $repository->getByCommentUuid(new UUID('123e4567-e89b-12d3-a456-426614174003'));

        $this->assertSame('123e4567-e89b-12d3-a456-426614174001', (string)$likes[0]->uuid());
    }

    public function testItTrowsAnExceptionsWhenCommentLikesNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetchAll')->willReturn([]);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentsLikesRepository($connectionStub, new DummyLogger());

        $this->expectException(CommentLikesNotFoundException::class);
        $this->expectExceptionMessage('Лайки комментария не найдены: 123e4567-e89b-12d3-a456-426614174000');

        $repository->getByCommentUuid(new UUID('123e4567-e89b-12d3-a456-426614174000'));
    }
}