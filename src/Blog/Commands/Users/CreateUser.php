<?php

namespace GeekBrains\LevelTwo\Blog\Commands\Users;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends Command
{
    public function __construct(
        // Внедряем репозиторий пользователей
        private UsersRepositoryInterface $usersRepository,
    )
    {
        // Вызываем родительский конструктор
        parent::__construct();
    }

    // Метод для конфигурации команды
    protected function configure(): void
    {
        $this
            // Указываем имя команды; мы будем запускать команду, используя это имя
            ->setName('users:create')

            // Описание команды
            ->setDescription('Создание нового пользователя')

            // Перечисляем аргументы команды
            ->addArgument(
                // Имя аргумента; его значение будет доступно по этому имени
                'first_name',
                // Указание того, что аргумент обязательный
                InputArgument::REQUIRED,
                // Описание аргумента
                'Имя'
            )

            // Описываем остальные аргументы
            ->addArgument('last_name', InputArgument::REQUIRED, 'Фамилия')
            ->addArgument('username', InputArgument::REQUIRED, 'Имя пользователя(логин)')
            ->addArgument('password', InputArgument::REQUIRED, 'Пароль');
    }

    // Метод, который будет запущен при вызове команды. В метод будет передан объект типа InputInterface,
    // содержащий значения аргументов; и объект типа OutputInterface, имеющий методы для форматирования и вывода сообщений
    protected function execute(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        // Для вывода сообщения вместо логгера используем объект типа OutputInterface
        $output->writeln('Команда создания пользователя запущена');

        // Вместо использования нашего класса Arguments получаем аргументы из объекта типа InputInterface
        $username = $input->getArgument('username');

        if ($this->userExists($username)) {
            // Используем OutputInterface вместо логгера
            $output->writeln("Пользователь уже существует: $username");
            // Завершаем команду с ошибкой
            return Command::FAILURE;
        }

        // Перенесли из класса CreateUserCommand. Вместо Arguments используем InputInterface
        $user = User::createFrom(
            $username,
            $input->getArgument('password'),
            new Name(
                $input->getArgument('first_name'),
                $input->getArgument('last_name')
            )
        );

        $this->usersRepository->save($user);

        // Используем OutputInterface вместо логгера
        $output->writeln('Пользователь создан: ' . $user->uuid());

        // Возвращаем код успешного завершения
        return Command::SUCCESS;
    }

    // Полностью перенесли из класса CreateUserCommand
    private function userExists(string $username): bool
    {
        try {
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }
}