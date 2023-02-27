<?php

namespace GeekBrains\LevelTwo\Blog\Commands\FakeData;

use Faker\Generator;
use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDB extends Command
{
    // Внедряем генератор тестовых данных и репозитории пользователей и статей
    public function __construct(
        private Generator                   $faker,
        private UsersRepositoryInterface    $usersRepository,
        private PostsRepositoryInterface    $postsRepository,
        private CommentsRepositoryInterface $commentsRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('fake-data:populate-db')
            ->setDescription('Заполняет БД поддельными данными')
            ->addOption(
                'users-number',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Количество создаваемых пользователей'
            )
            ->addOption(
                'posts-number',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Количество создаваемых статей для каждого пользователя'
            )
            ->addOption(
                'comments-number',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Количество комментариев от каждого пользователя к каждой статье'
            );
    }

    protected function execute(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        $usersNumber = $input->getOption('users-number');
        $postsNumber = $input->getOption('posts-number');
        $commentsNumber = $input->getOption('comments-number');

        if (empty($usersNumber)) {
            $output->writeln('Пользователи для создания отсутствуют');
            return Command::SUCCESS;
        }

        $users = [];
        for ($i = 0; $i < $usersNumber; $i++) {
            $user = $this->createFakeUser();
            $users[] = $user;
            $this->usersRepository->save($user);
            $output->writeln('Пользователь создан: ' . $user->username());
        }

        if (empty($postsNumber)) {
            $output->writeln('Посты для создания отсутствуют');
            return Command::SUCCESS;
        }

        $posts = [];
        foreach ($users as $user) {
            for ($i = 0; $i < $postsNumber; $i++) {
                $post = $this->createFakePost($user);
                $posts[] = $post;
                $this->postsRepository->save($post);
                $output->writeln('Пост создан: ' . $post->title());
            }
        }

        if (empty($commentsNumber)) {
            $output->writeln('Комментарии для создания отсутствуют');
            return Command::SUCCESS;
        }

        foreach ($users as $user) {
            foreach ($posts as $post) {
                for ($i = 0; $i < $commentsNumber; $i++) {
                    $comment = $this->createFakeComment($user, $post);
                    $this->commentsRepository->save($comment);
                    $output->writeln('Комментарий создан: ' . $comment->text());
                }
            }
        }

        return Command::SUCCESS;
    }

    private function createFakeUser(): User
    {
        $user = User::createFrom(
        // Генерируем имя пользователя
            $this->faker->userName,
            // Генерируем пароль
            $this->faker->password,
            new Name(
            // Генерируем имя
                $this->faker->firstName,
                // Генерируем фамилию
                $this->faker->lastName
            )
        );

        return $user;
    }

    private function createFakePost(User $author): Post
    {
        $post = new Post(
            UUID::random(),
            $author,
            // Генерируем предложение не длиннее шести слов
            $this->faker->sentence(6, true),
            // Генерируем текст
            $this->faker->realText
        );

        return $post;
    }

    private function createFakeComment(User $author, Post $post): Comment
    {
        $comment = new Comment(
            UUID::random(),
            $post,
            $author,
            $this->faker->realText(100, 2)
        );

        return $comment;
    }
}