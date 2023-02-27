<?php

namespace GeekBrains\LevelTwo\Blog\Commands\Posts;

use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeletePost extends Command
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
    )
    {
        parent::__construct();
    }

    // Конфигурируем команду
    protected function configure(): void
    {
        $this
            ->setName('posts:delete')
            ->setDescription('Удаление поста')
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID удаляемого поста'
            )
            // Добавили опцию
            ->addOption(
                // Имя опции
                'check-existence',
                // Сокращённое имя
                'c',
                // Опция не имеет значения
                InputOption::VALUE_NONE,
                // Описание
                'Проверяет существование поста',
            );
    }

    protected function execute(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        $question = new ConfirmationQuestion(
        // Вопрос для подтверждения
            'Удалить пост [y/N]? ',
            // По умолчанию не удалять
            false
        );
        // Ожидаем подтверждения
        if (!$this->getHelper('question')
            ->ask($input, $output, $question)
        ) {
            // Выходим, если удаление не подтверждено
            return Command::SUCCESS;
        }

        // Получаем UUID статьи
        $uuid = new UUID($input->getArgument('uuid'));

        // Если опция проверки существования статьи установлена
        if ($input->getOption('check-existence')) {
            try {
                // Пытаемся получить статью
                $this->postsRepository->get($uuid);
            } catch (PostNotFoundException $e) {
                // Выходим, если статья не найдена
                $output->writeln($e->getMessage());
                return Command::FAILURE;
            }
        }

        // Удаляем статью из репозитория
        $this->postsRepository->delete($uuid);
        $output->writeln("Пост $uuid удалён");
        return Command::SUCCESS;
    }
}