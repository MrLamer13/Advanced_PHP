<?php

use GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository\CommentsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository\SqliteCommentsLikesRepository;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository\PostsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository\SqlitePostsLikesRepository;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Container\DIContainer;

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

// Создаём объект контейнера ..
$container = new DIContainer();

// .. и настраиваем его:
// 1. подключение к БД
$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
);

// 2. репозиторий статей
$container->bind(
    PostsRepositoryInterface::class,
    SqlitePostsRepository::class
);

// 3. репозиторий пользователей
$container->bind(
    UsersRepositoryInterface::class,
    SqliteUsersRepository::class
);

// 4. репозиторий комментариев
$container->bind(
    CommentsRepositoryInterface::class,
    SqliteCommentsRepository::class
);

// 5. репозиторий лайков постов
$container->bind(
    PostsLikesRepositoryInterface::class,
    SqlitePostsLikesRepository::class
);

// 6. репозиторий лайков комментариев
$container->bind(
    CommentsLikesRepositoryInterface::class,
    SqliteCommentsLikesRepository::class
);

// Возвращаем объект контейнера
return $container;