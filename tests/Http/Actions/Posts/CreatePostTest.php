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
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUsernameAuthentication;
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUuidAuthentication;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\UnitTests\DummyLogger;
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
                '123',
                new Name('Vasia', 'Pupkin')
            ),
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "title": "Title", "text": "Post text"}');

        $action = new CreatePost($postsRepository, $identification, new DummyLogger());
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();

    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfRequestNotContainAllData(): void
    {
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                'vasia',
                '123',
                new Name('Vasia', 'Pupkin')
            ),
        ]);

        $identification = new JsonBodyUuidAuthentication($usersRepository);

        $request = new Request([], [],
            '{"user_uuid": "123e4567-e89b-12d3-a456-426614174000", "title": "Title"}');

        $action = new CreatePost($postsRepository, $identification, new DummyLogger());
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Нет такого поля: text"}');
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
                throw new PostNotFoundException('Не найдено');
            }
        };
    }

    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid == $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Не найдено");
            }

            public function getByUsername(string $username): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $username === $user->username()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Не найдено");
            }
        };
    }
}