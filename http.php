<?php

use GeekBrains\LevelTwo\Exceptions\HttpException;
use GeekBrains\LevelTwo\Exceptions\AppException;
use GeekBrains\LevelTwo\Http\Actions\Comments\CreateComment;
use GeekBrains\LevelTwo\Http\Actions\CommentsLikes\CreateCommentLike;
use GeekBrains\LevelTwo\Http\Actions\CommentsLikes\FindCommentLikes;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
use GeekBrains\LevelTwo\Http\Actions\Posts\DeletePost;
use GeekBrains\LevelTwo\Http\Actions\Posts\FindByUuid;
use GeekBrains\LevelTwo\Http\Actions\PostsLikes\CreatePostLike;
use GeekBrains\LevelTwo\Http\Actions\PostsLikes\FindPostLikes;
use GeekBrains\LevelTwo\Http\Actions\Users\CreateUser;
use GeekBrains\LevelTwo\Http\Actions\Users\FindByUsername;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;

$container = require __DIR__ . '/bootstrap.php';

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);

try {
// Пытаемся получить путь из запроса
    $path = $request->path();
} catch (HttpException) {
// Отправляем неудачный ответ, если по какой-то причине не можем получить путь
    (new ErrorResponse)->send();
// Выходим из программы
    return;
}

try {
// Пытаемся получить HTTP-метод запроса
    $method = $request->method();
} catch (HttpException) {
// Возвращаем неудачный ответ, если по какой-то причине не можем получить метод
    (new ErrorResponse)->send();
    return;
}

$routes = [
    'GET' => [
        '/users/show' => FindByUsername::class,
        '/posts/show' => FindByUuid::class,
        '/posts/like/show' => FindPostLikes::class,
        '/posts/comment/like/show' => FindCommentLikes::class
    ],
    'POST' => [
        '/posts/create' => CreatePost::class,
        '/users/create' => CreateUser::class,
        '/posts/comment' => CreateComment::class,
        '/posts/like' => CreatePostLike::class,
        '/posts/comment/like' => CreateCommentLike::class
    ],
    'DELETE' => [
        '/posts' => DeletePost::class
    ]
];

// Если у нас нет маршрутов для метода запроса - возвращаем неуспешный ответ
if (!array_key_exists($method, $routes)) {
    (new ErrorResponse('Метод не найден: $method $path'))->send();
    return;
}

// Ищем маршрут среди маршрутов для этого метода
if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Маршрут не найден: $method $path'))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

// С помощью контейнера создаём объект нужного действия
$action = $container->get($actionClassName);

try {
    $response = $action->handle($request);
    $response->send();
} catch (AppException $e) {
    (new ErrorResponse($e->getMessage()))->send();
}

