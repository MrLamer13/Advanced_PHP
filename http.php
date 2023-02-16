<?php

use GeekBrains\LevelTwo\Exceptions\HttpException;
use GeekBrains\LevelTwo\Exceptions\AppException;
use GeekBrains\LevelTwo\Http\Actions\Auth\LogIn;
use GeekBrains\LevelTwo\Http\Actions\Auth\LogOut;
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
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);

$logger = $container->get(LoggerInterface::class);

try {
    // Пытаемся получить путь из запроса
    $path = $request->path();
} catch (HttpException $exception) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($exception->getMessage());
    // Отправляем неудачный ответ, если по какой-то причине не можем получить путь
    (new ErrorResponse)->send();
    // Выходим из программы
    return;
}

try {
    // Пытаемся получить HTTP-метод запроса
    $method = $request->method();
} catch (HttpException $exception) {
    $logger->warning($exception->getMessage());
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
        '/login' => LogIn::class,
        '/logout' => LogOut::class,
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

if (!array_key_exists($method, $routes) || !array_key_exists($path, $routes[$method])) {
    // Логируем сообщение с уровнем NOTICE
    $message = "Маршрут не найден: $method $path";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

try {
    $action = $container->get($actionClassName);
    $response = $action->handle($request);
    $response->send();
} catch (AppException $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
    (new ErrorResponse())->send();
    return;
}

