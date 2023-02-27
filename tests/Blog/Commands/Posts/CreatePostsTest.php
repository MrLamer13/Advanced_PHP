<?php

namespace GeekBrains\LevelTwo\UnitTests\Blog\Commands\Posts;

use GeekBrains\LevelTwo\Blog\Commands\Posts\CreatePost;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreatePostsTest extends TestCase
{
    public function testItSavesPostToRepository(): void
    {
        $usersRepository = $this->makeUsersRepository();
        $postsRepository = $this->makePostsRepository();

        $command = new CreatePost(
            $postsRepository,
            $usersRepository
        );

        $command->run(
            new ArrayInput([
                'author_username' => 'Ivan',
                'title' => 'Title',
                'text' => 'text'
            ]),
            new NullOutput()
        );

        $this->assertTrue($postsRepository->wasCalled());
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
            private bool $called = false;

            public function save(Post $post): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException('Не найдено');
            }

            public function delete(UUID $uuid): void
            {
                throw new PostNotFoundException('Не найдено');
            }

            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
    }
}