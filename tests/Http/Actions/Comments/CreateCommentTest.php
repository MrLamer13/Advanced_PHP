<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\Comments;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\Comments\CreateComment;
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUsernameAuthentication;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class CreateCommentTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        );
        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174001'),
                $user,
                'Заголовок',
                'Статья'
            )
        ]);
        $usersRepository = $this->usersRepository([
            $user
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"post_uuid": "123e4567-e89b-12d3-a456-426614174001", "username": "vasia", "text": "Комментарий"}');

        $action = new CreateComment($postsRepository, $identification, $commentsRepository);
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
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        );
        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174001'),
                $user,
                'Заголовок',
                'Статья'
            )
        ]);
        $usersRepository = $this->usersRepository([
            $user
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"post_uuid": "123e4567-e89b-12d3-a456-426614174001", "username": "vasia"}');

        $action = new CreateComment($postsRepository, $identification, $commentsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Нет такого поля: text"}');
        $response->send();

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfPostNotFound(): void
    {
        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                'vasia',
                '123',
                new Name('Vasia', 'Pupkin')
            )
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"post_uuid": "123e4567-e89b-12d3-a456-426614174001", "username": "vasia", "text": "Комментарий"}');

        $action = new CreateComment($postsRepository, $identification, $commentsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найдено"}');
        $response->send();

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfInvalidPostUUID(): void
    {
        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                'vasia',
                '123',
                new Name('Vasia', 'Pupkin')
            )
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"post_uuid": "123", "username": "vasia", "text": "Комментарий"}');

        $action = new CreateComment($postsRepository, $identification, $commentsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Неправильный UUID: 123"}');
        $response->send();

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfInvalidAuthor(): void
    {
        $commentsRepository = $this->commentsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"post_uuid": "123e4567-e89b-12d3-a456-426614174001", "username": "vasia", "text": "Комментарий"}');

        $action = new CreateComment($postsRepository, $identification, $commentsRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найдено"}');
        $response->send();

    }

    private function commentsRepository(array $comments): CommentsRepositoryInterface
    {
        return new class($comments) implements CommentsRepositoryInterface {
          public function __construct(
              private array $comments
          )
          {
          }


            public function save(Comment $comment): void
            {
                $this->comments[] = $comment;
            }

            public function get(UUID $uuid): Comment
            {
                throw new CommentNotFoundException('Не найдено');
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
                throw new PostNotFoundException('Не найдено');
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