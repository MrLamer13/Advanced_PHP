<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\PostsLikes;

use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\PostLike;
use GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository\PostsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostLikesNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\PostsLikes\FindPostLikes;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class FindPostLikesTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request(['post_uuid' => '123e4567-e89b-12d3-a456-426614174000'], [], '');
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174001'),
            'ivan',
            '123',
            new Name('Ivan', 'Nikitin')
        );
        $postsLikesRepository = $this->postsLikesRepository([
           new PostLike(
               new UUID('123e4567-e89b-12d3-a456-426614174004'),
               new Post(
                   new UUID('123e4567-e89b-12d3-a456-426614174000'),
                   $user,
                   'Заголовок поста',
                   'Текст поста'
               ),
               $user
           )
        ]);

        $action = new FindPostLikes($postsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"post: 123e4567-e89b-12d3-a456-426614174000":[{"uuid":"123e4567-e89b-12d3-a456-426614174004","author":"ivan"}]}}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfNoUuidProvided(): void
    {
        $request = new Request([], [], '');
        $postsLikesRepository = $this->postsLikesRepository([]);

        $action = new FindPostLikes($postsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Нет такого параметра запроса в запросе: post_uuid"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfUuidNotFound(): void
    {
        $request = new Request(['post_uuid' => '123e4567-e89b-12d3-a456-426614174000'], [], '');
        $postsLikesRepository = $this->postsLikesRepository([]);

        $action = new FindPostLikes($postsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найдено"}');
        $response->send();
    }

    private function postsLikesRepository(array $postsLikes): PostsLikesRepositoryInterface
    {
        return new class($postsLikes) implements PostsLikesRepositoryInterface {
            public function __construct(
                private array $postsLikes
            )
            {
            }

            public function save(PostLike $postLike): void
            {
                $this->postsLikes[] = $postLike;
            }

            public function getByPostUuid(UUID $postUuid): array
            {
                foreach ($this->postsLikes as $postLike) {
                    if ($postLike instanceof PostLike && (string)$postUuid == $postLike->post()->uuid()) {
                        return [$postLike];
                    }
                }
                throw new PostLikesNotFoundException('Не найдено');
            }
        };
    }
}