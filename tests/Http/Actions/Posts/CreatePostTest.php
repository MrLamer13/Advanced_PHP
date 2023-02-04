<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class CreatePostTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                'vasia',
                new Name('Vasia', 'Pupkin')
            ),
        ]);

        $request = new Request([], [],
            '{"author_uuid": "123e4567-e89b-12d3-a456-426614174000", "title": "Title", "text": "Post text"}');

        $action = new CreatePost($postsRepository, $usersRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testReturnsErrorResponseIfWrongUuid(): void
    {
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $request = new Request([], [],
            '{"author_uuid": "123", "title": "Title", "text": "Post text"}');

        $action = new CreatePost($postsRepository, $usersRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Неправильный UUID: 123"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfNotUserByUuid(): void
    {
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $request = new Request([], [],
            '{"author_uuid": "123e4567-e89b-12d3-a456-426614174000", "title": "Title", "text": "Post text"}');

        $action = new CreatePost($postsRepository, $usersRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найдено"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfRequestNotContainAllData(): void
    {
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                'vasia',
                new Name('Vasia', 'Pupkin')
            ),
        ]);

        $request = new Request([], [],
            '{"author_uuid": "123e4567-e89b-12d3-a456-426614174000", "title": "Title"}');

        $action = new CreatePost($postsRepository, $usersRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Нет такого поля: text"}');
        $response->send();
    }





    private function postsRepository(array $posts): PostsRepositoryInterface
    {
        return new class($posts) implements PostsRepositoryInterface
        {
            public function __construct(
                private array $posts
            )
            {            }

            public function save(Post $post): void
            {
                $this->posts[] = $post;
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException('Не найдено');
            }
        };
    }

    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface {
            public function __construct(
                private array $users
            ) {
            }
            public function save(User $user): void
            {
            }
            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid == $user->uuid())
                    {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Не найдено");
            }
            public function getByUsername(string $username): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $username === $user->username())
                    {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Не найдено");
            }
        };
    }
}