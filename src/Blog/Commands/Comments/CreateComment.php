<?php

namespace GeekBrains\LevelTwo\Blog\Commands\Comments;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateComment extends Command
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository,
        private PostsRepositoryInterface    $postsRepository,
        private UsersRepositoryInterface    $usersRepository
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('comments:create')
            ->setDescription('Создание нового комментария')
            ->addArgument('author_username', InputArgument::REQUIRED, 'Логин автора комментария')
            ->addArgument('post_uuid', InputArgument::REQUIRED, 'UUID комментируемого поста')
            ->addArgument('text', InputArgument::REQUIRED, 'Текст комментария');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int
    {
        $output->writeln('Команда создания комментария запущена');

        $authorUsername = $input->getArgument('author_username');

        try {
            $author = $this->usersRepository->getByUsername($authorUsername);
        } catch (UserNotFoundException $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }

        $postUuid = $input->getArgument('post_uuid');

        try {
            $post = $this->postsRepository->get(new UUID($postUuid));
        } catch (PostNotFoundException $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }

        $comment = new Comment(
            UUID::random(),
            $post,
            $author,
            $input->getArgument('text')
        );

        $this->commentsRepository->save($comment);

        $output->writeln('Комментарий создан: ' . $comment->text());

        return Command::SUCCESS;
    }
}