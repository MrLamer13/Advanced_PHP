<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\Posts\DeletePost;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class DeletePostTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $postsRepository = $this->postsRepository([
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174001'),
                new User(
                    new UUID('123e4567-e89b-12d3-a456-426614174000'),
                    'vasia',
                    '123',
                    new Name('Vasia', 'Pupkin')
                ),
                'Заголовок',
                'Статья'
            )
        ]);

        $request = new Request(["uuid" => "123e4567-e89b-12d3-a456-426614174001"],
            [],
            ''
            );

        $action = new DeletePost($postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfInvalidPostUUID(): void
    {
        $postsRepository = $this->postsRepository([]);

        $request = new Request(["uuid" => "123"],
            [],
            ''
        );

        $action = new DeletePost($postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Неправильный UUID: 123"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfInvalidParam(): void
    {
        $postsRepository = $this->postsRepository([]);

        $request = new Request([],
            [],
            ''
        );

        $action = new DeletePost($postsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Нет такого параметра запроса в запросе: uuid"}');
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
                throw new PostNotFoundException('Не найдено');
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }
}