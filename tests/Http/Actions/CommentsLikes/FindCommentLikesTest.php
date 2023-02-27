<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\CommentsLikes;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\CommentLike;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository\CommentsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentLikesNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\CommentsLikes\FindCommentLikes;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class FindCommentLikesTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request(['comment_uuid' => '123e4567-e89b-12d3-a456-426614174002'], [], '');
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174001'),
            'ivan',
            '123',
            new Name('Ivan', 'Nikitin')
        );
        $post = new Post(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            $user,
            'Заголовок поста',
            'Текст поста'
        );
        $commentsLikesRepository = $this->commentsLikesRepository([
            new CommentLike(
                new UUID('123e4567-e89b-12d3-a456-426614174004'),
                new Comment(
                    new UUID('123e4567-e89b-12d3-a456-426614174002'),
                    $post,
                    $user,
                    'Комментарий'
                ),
                $user
            )
        ]);

        $action = new FindCommentLikes($commentsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"comment: 123e4567-e89b-12d3-a456-426614174002":[{"uuid":"123e4567-e89b-12d3-a456-426614174004","author":"ivan"}]}}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfNoUuidProvided(): void
    {
        $request = new Request([], [], '');
        $commentsLikesRepository = $this->commentsLikesRepository([]);

        $action = new FindCommentLikes($commentsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Нет такого параметра запроса в запросе: comment_uuid"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfUuidNotFound(): void
    {
        $request = new Request(['comment_uuid' => '123e4567-e89b-12d3-a456-426614174000'], [], '');
        $commentsLikesRepository = $this->commentsLikesRepository([]);

        $action = new FindCommentLikes($commentsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найдено"}');
        $response->send();
    }

    private function commentsLikesRepository(array $commentsLikes): CommentsLikesRepositoryInterface
    {
        return new class($commentsLikes) implements CommentsLikesRepositoryInterface {
            public function __construct(
                private array $commentsLikes
            )
            {
            }

            public function save(CommentLike $commentLike): void
            {
                $this->commentsLikes[] = $commentLike;
            }

            public function getByCommentUuid(UUID $commentUuid): array
            {
                foreach ($this->commentsLikes as $commentLike) {
                    if ($commentLike instanceof CommentLike && (string)$commentUuid == $commentLike->comment()->uuid()) {
                        return [$commentLike];
                    }
                }
                throw new CommentLikesNotFoundException('Не найдено');
            }
        };
    }
}