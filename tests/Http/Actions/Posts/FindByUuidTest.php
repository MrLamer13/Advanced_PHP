<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\Posts\FindByUuid;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class FindByUuidTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfNoUuidProvided(): void
    {
        $request = new Request([], [], '');
        $postsRepository = $this->postsRepository([]);

        $action = new FindByUuid($postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Нет такого параметра запроса в запросе: uuid"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfUuidIsEmpty(): void
    {
        $request = new Request(['uuid' => ''], [], '');
        $postsRepository = $this->postsRepository([]);

        $action = new FindByUuid($postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Пустой параметр запроса в запросе: uuid"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfUuidNotFound(): void
    {
        $request = new Request(['uuid' => '123e4567-e89b-12d3-a456-426614174000'], [], '');
        $postsRepository = $this->postsRepository([]);

        $action = new FindByUuid($postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найдено"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfWrongUuid(): void
    {
        $request = new Request(['uuid' => '123'], [], '');
        $postsRepository = $this->postsRepository([]);

        $action = new FindByUuid($postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Неправильный UUID: 123"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request(['uuid' => '123e4567-e89b-12d3-a456-426614174000'], [], '');
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174001'),
            'ivan',
            new Name('Ivan', 'Nikitin')
        );
        $postsRepository = $this->postsRepository([
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $user,
                'Заголовок поста',
                'Текст поста'
            )
        ]);

        $action = new FindByUuid($postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"123e4567-e89b-12d3-a456-426614174000","author":"ivan","title":"Заголовок поста","text":"Текст поста"}}');
        $response->send();
    }



    private function postsRepository(array $posts): PostsRepositoryInterface
    {
        return new class($posts) implements PostsRepositoryInterface {
            public function __construct(
                private array $posts
            )
            {
            }

            public function save(Post $post): void
            {
                $this->posts[] = $post;
            }

            public function get(UUID $uuid): Post
            {
                foreach ($this->posts as $post) {
                    if ($post instanceof Post && (string)$uuid == $post->uuid()) {
                        return $post;
                    }
                }
                throw new PostNotFoundException("Не найдено");
            }
        };
    }

}