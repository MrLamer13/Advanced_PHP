<?php

namespace GeekBrains\LevelTwo\Blog\Commands\Posts;

use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePost extends Command
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private UsersRepositoryInterface $usersRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('posts:create')
            ->setDescription('Создание нового поста')
            ->addArgument('author_username', InputArgument::REQUIRED, 'Логин автора поста')
            ->addArgument('title', InputArgument::REQUIRED, 'Заголовок поста')
            ->addArgument('text', InputArgument::REQUIRED, 'Текст поста');
    }

    protected function execute(
        InputInterface  $input,
        OutputInterface $output
    ): int
    {
        $output->writeln('Команда создания поста запущена');

        $authorUsername = $input->getArgument('author_username');

        try {
            $author = $this->usersRepository->getByUsername($authorUsername);
        } catch (UserNotFoundException $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }

        $post = new Post(
            UUID::random(),
            $author,
            $input->getArgument('title'),
            $input->getArgument('text')
        );

        $this->postsRepository->save($post);

        $output->writeln('Пост создан: ' . $post->title());

        return Command::SUCCESS;

    }
}