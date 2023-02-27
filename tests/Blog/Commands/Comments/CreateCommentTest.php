<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Commands\Comments;

use GeekBrains\LevelTwo\Blog\Commands\Comments\CreateComment;
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
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreateCommentTest extends TestCase
{
    public function testItSavesCommentToRepository(): void
    {
        $usersRepository = $this->makeUsersRepository();
        $postsRepository = $this->makePostsRepository();
        $commentsRepository = $this->makeCommentsRepository();
        $command = new CreateComment(
            $commentsRepository,
            $postsRepository,
            $usersRepository
        );

        $command->run(
            new ArrayInput([
                'author_username' => 'Ivan',
                'post_uuid' => '4998a26e-2955-4ccf-8f77-ed722699fb3e',
                'text' => 'Comment'
            ]),
            new NullOutput()
        );

        $this->assertTrue($commentsRepository->wasCalled());
    }

    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {

            public function save(User $user): void
            {
                throw new UserNotFoundException('Не найдено');
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException('Не найдено');
            }

            public function getByUsername(string $username): User
            {
                return new User(
                    new UUID('9c4ec61b-747f-45ef-b66a-28bc61d0fe0d'),
                    'Ivan',
                    '123',
                    new Name(
                        'Ivan',
                        'Pupkin'
                    )
                );
            }
        };
    }

    private function makePostsRepository(): PostsRepositoryInterface
    {
        return new class implements PostsRepositoryInterface {

            public function save(Post $post): void
            {
                throw new PostNotFoundException('Не найдено');
            }

            public function get(UUID $uuid): Post
            {
                return new Post(
                    new UUID('4998a26e-2955-4ccf-8f77-ed722699fb3e'),
                    new User(
                        new UUID('9c4ec61b-747f-45ef-b66a-28bc61d0fe0d'),
                        'Ivan',
                        '123',
                        new Name(
                            'Ivan',
                            'Pupkin'
                        )
                    ),
                    'title',
                    'text'
                );
            }

            public function delete(UUID $uuid): void
            {
                throw new PostNotFoundException('Не найдено');
            }
        };
    }

    private function makeCommentsRepository(): CommentsRepositoryInterface
    {
        return new class implements CommentsRepositoryInterface {
            private bool $called = false;

            public function save(Comment $post): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Comment
            {
                throw new CommentNotFoundException('Не найдено');
            }

            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
    }
}