<?php

namespace GeekBrains\LevelTwo\Blog\Commands\Users;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('users:update')
            ->setDescription('Обновление данных пользователя')
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID пользователя для обновления данных'
            )
            ->addOption(
            // Имя опции
                'first-name',
                // Сокращённое имя
                'f',
                // Опция имеет значения
                InputOption::VALUE_OPTIONAL,
                // Описание
                'Имя',
            )
            ->addOption(
                'last-name',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Фамилия',
            );
    }

    protected function execute(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        // Получаем значения опций
        $firstName = $input->getOption('first-name');
        $lastName = $input->getOption('last-name');

        // Выходим, если обе опции пусты
        if (empty($firstName) && empty($lastName)) {
            $output->writeln('Нечего обновлять');
            return Command::SUCCESS;
        }

        // Получаем UUID из аргумента
        $uuid = new UUID($input->getArgument('uuid'));

        // Получаем пользователя из репозитория
        $user = $this->usersRepository->get($uuid);

        // Создаём объект обновлённого имени
        $updatedName = new Name(
        // Берём сохранённое имя, если опция имени пуста
            firstName: empty($firstName) ? $user->name()->first() : $firstName,
            // Берём сохранённую фамилию, если опция фамилии пуста
            lastName: empty($lastName) ? $user->name()->last() : $lastName,
        );

        // Создаём новый объект пользователя
        $updatedUser = new User(
            uuid: $uuid,
            // Имя пользователя и пароль оставляем без изменений
            username: $user->username(),
            hashedPassword: $user->hashedPassword(),
            // Обновлённое имя
            name: $updatedName
        );

        // Сохраняем обновлённого пользователя
        $this->usersRepository->save($updatedUser);
        $output->writeln("Данные пользователя обновлены: $uuid");

        return Command::SUCCESS;
    }
}