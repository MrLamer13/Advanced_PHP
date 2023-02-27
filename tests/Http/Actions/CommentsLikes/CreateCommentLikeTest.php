<?php

namespace GeekBrains\LevelTwo\UnitTests\Http\Actions\CommentsLikes;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\CommentLike;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository\CommentsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentLikeExistException;
use GeekBrains\LevelTwo\Exceptions\CommentLikesNotFoundException;
use GeekBrains\LevelTwo\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\CommentsLikes\CreateCommentLike;
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUsernameAuthentication;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;

class CreateCommentLikeTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $commentsLikesRepository = $this->commentsLikesRepository([]);
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
        $commentsRepository = $this->commentsRepository([
            new Comment(
                new UUID('123e4567-e89b-12d3-a456-426614174003'),
                $post,
                $user,
                'Комментарий'
            )
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "comment_uuid": "123e4567-e89b-12d3-a456-426614174003"}');

        $action = new CreateCommentLike($commentsRepository, $identification, $commentsLikesRepository);
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
        $comment = new Comment(
            new UUID('123e4567-e89b-12d3-a456-426614174003'),
            $post,
            $user,
            'Комментарий'
        );
        $commentsLikesRepository = $this->commentsLikesRepository([
            new CommentLike(
                UUID::random(),
                $comment,
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
        $commentsRepository = $this->commentsRepository([
            $comment
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "comment_uuid": "123e4567-e89b-12d3-a456-426614174003"}');

        $action = new CreateCommentLike($commentsRepository, $identification, $commentsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfCommentNotFound(): void
    {
        $commentsLikesRepository = $this->commentsLikesRepository([]);
        $usersRepository = $this->usersRepository([new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        )]);
        $commentsRepository = $this->commentsRepository([]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "comment_uuid": "123e4567-e89b-12d3-a456-426614174003"}');

        $action = new CreateCommentLike($commentsRepository, $identification, $commentsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найден комментарий"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfInvalidAuthor(): void
    {
        $commentsLikesRepository = $this->commentsLikesRepository([]);
        $usersRepository = $this->usersRepository([]);
        $commentsRepository = $this->commentsRepository([]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "comment_uuid": "123e4567-e89b-12d3-a456-426614174003"}');

        $action = new CreateCommentLike($commentsRepository, $identification, $commentsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Не найден пользователь"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfInvalidCommentUUID(): void
    {
        $commentsLikesRepository = $this->commentsLikesRepository([]);
        $usersRepository = $this->usersRepository([new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'vasia',
            '123',
            new Name('Vasia', 'Pupkin')
        )]);
        $commentsRepository = $this->commentsRepository([]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "comment_uuid": "123"}');

        $action = new CreateCommentLike($commentsRepository, $identification, $commentsLikesRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Неправильный UUID: 123"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsThrowIfCommentLikeIsExist(): void
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
        $comment = new Comment(
            new UUID('123e4567-e89b-12d3-a456-426614174003'),
            $post,
            $user,
            'Комментарий'
        );
        $commentsLikesRepository = $this->commentsLikesRepository([
            new CommentLike(
                UUID::random(),
                $comment,
                $user
            )
        ]);
        $usersRepository = $this->usersRepository([
            $user
        ]);
        $commentsRepository = $this->commentsRepository([
            $comment
        ]);

        $identification = new JsonBodyUsernameAuthentication($usersRepository);

        $request = new Request([], [],
            '{"username": "vasia", "comment_uuid": "123e4567-e89b-12d3-a456-426614174003"}');

        $action = new CreateCommentLike($commentsRepository, $identification, $commentsLikesRepository);

        $this->expectException(CommentLikeExistException::class);
        $this->expectExceptionMessage('Лайк уже поставлен');
        $response = $action->handle($request);
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
            }

            public function get(UUID $uuid): Comment
            {
                foreach ($this->comments as $comment) {
                    if ($comment instanceof Comment && (string)$uuid == $comment->uuid()) {
                        return $comment;
                    }
                }
                throw new CommentNotFoundException('Не найден комментарий');
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