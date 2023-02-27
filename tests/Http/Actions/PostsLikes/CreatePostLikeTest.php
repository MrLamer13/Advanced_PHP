<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\PostsLikes;

use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\PostLike;
use GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository\PostsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostLikeExistException;
use GeekBrains\LevelTwo\Exceptions\PostLikesNotFoundException;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\PostsLikes\CreatePostLike;
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUsernameAuthentication;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class CreatePostLikeTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $postsLikesRepository = $this->postsLikesRepository([]);
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        );
        $usersRepository = $this->usersRepository([
            $user
        ]);
        $postsRepository = $this->postsRepository([
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174001'),
                $user,
                'Заголовок',
                'Статья'
            )
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "post_uuid": "123e4567-e89b-12d3-a456-426614174001"}');

        $action = new CreatePostLike($postsRepository, $identification, $postsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponseWithLike(): void
    {
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        );
        $post = new Post(
            new UUID('123e4567-e89b-12d3-a456-426614174001'),
            $user,
            'Заголовок',
            'Статья'
        );
        $postsLikesRepository = $this->postsLikesRepository([
            new PostLike(
                UUID::random(),
                $post,
                new User(
                    UUID::random(),
                    'pupok',
                    '123',
                    new Name('Pupok', 'Vasin')
                )
            )
        ]);
        $usersRepository = $this->usersRepository([
            $user
        ]);
        $postsRepository = $this->postsRepository([
            $post
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "post_uuid": "123e4567-e89b-12d3-a456-426614174001"}');

        $action = new CreatePostLike($postsRepository, $identification, $postsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfPostNotFound(): void
    {
        $postsLikesRepository = $this->postsLikesRepository([]);
        $usersRepository = $this->usersRepository([new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        )]);
        $postsRepository = $this->postsRepository([]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "post_uuid": "123e4567-e89b-12d3-a456-426614174001"}');

        $action = new CreatePostLike($postsRepository, $identification, $postsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найден пост"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfInvalidAuthor(): void
    {
        $postsLikesRepository = $this->postsLikesRepository([]);
        $usersRepository = $this->usersRepository([]);
        $postsRepository = $this->postsRepository([]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "post_uuid": "123e4567-e89b-12d3-a456-426614174001"}');

        $action = new CreatePostLike($postsRepository, $identification, $postsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найден пользователь"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfInvalidPostUUID(): void
    {
        $postsLikesRepository = $this->postsLikesRepository([]);
        $usersRepository = $this->usersRepository([new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        )]);
        $postsRepository = $this->postsRepository([]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "post_uuid": "123"}');

        $action = new CreatePostLike($postsRepository, $identification, $postsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Неправильный UUID: 123"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfPostLikeIsExist(): void
    {
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        );
        $post = new Post(
            new UUID('123e4567-e89b-12d3-a456-426614174001'),
            $user,
            'Заголовок',
            'Статья'
        );
        $usersRepository = $this->usersRepository([
            $user
        ]);
        $postsRepository = $this->postsRepository([
            $post
        ]);
        $postsLikesRepository = $this->postsLikesRepository([
            new PostLike(
                UUID::random(),
                $post,
                $user
            )
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "post_uuid": "123e4567-e89b-12d3-a456-426614174001"}');

        $action = new CreatePostLike($postsRepository, $identification, $postsLikesRepository);

        $this->expectException(PostLikeExistException::class);
        $this->expectExceptionMessage('Лайк уже поставлен');
        $response = $action->handle($request);
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
            }

            public function get(UUID $uuid): Post
            {
                foreach ($this->posts as $post) {
                    if ($post instanceof Post && (string)$uuid == $post->uuid()) {
                        return $post;
                    }
                }
                throw new PostNotFoundException('Не найден пост');
            }

            public function delete(UUID $uuid): void
            {
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
                throw new UserNotFoundException("Не найден пользователь");
            }

            public function getByUsername(string $username): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $username === $user->username()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Не найден пользователь");
            }
        };
    }
}