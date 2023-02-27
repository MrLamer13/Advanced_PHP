<?php

use GeekBrains\LevelTwo\Blog\Commands\Comments\CreateComment;
use GeekBrains\LevelTwo\Blog\Commands\FakeData\PopulateDB;
use GeekBrains\LevelTwo\Blog\Commands\Posts\CreatePost;
use GeekBrains\LevelTwo\Blog\Commands\Posts\DeletePost;
use GeekBrains\LevelTwo\Blog\Commands\Users\CreateUser;
use GeekBrains\LevelTwo\Blog\Commands\Users\UpdateUser;
use Symfony\Component\Console\Application;

$container = require __DIR__ . '/bootstrap.php';

// Создаём объект приложения
$application = new Application();

// Перечисляем классы команд
$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    PopulateDB::class,
    CreatePost::class,
    CreateComment::class,
];

foreach ($commandsClasses as $commandClass) {
    // Посредством контейнера создаём объект команды
    $command = $container->get($commandClass);
    // Добавляем команду к приложению
    $application->add($command);
}

// Запускаем приложение
$application->run();
