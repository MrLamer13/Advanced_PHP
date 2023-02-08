<?php

use GeekBrains\LevelTwo\Blog\Commands\Arguments;
use GeekBrains\LevelTwo\Blog\Commands\CreateUserCommand;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\AppException;

// Подключаем файл bootstrap.php и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

// При помощи контейнера создаём команду
$command = $container->get(CreateUserCommand::class);

try {
    $command->handle(Arguments::fromArgv($argv));
} catch (AppException $e) {
    echo "{$e->getMessage()}\n";
}

//try {

//$user = $usersRepository->get(new UUID('ea857322-6996-411f-b6af-5a65c3fca74a'));
//$user = $usersRepository->getByUsername('ivan');

//$post = new Post(
//    UUID::random(),
//    $user,
//    'Заголовок другой',
//    'Ещё какой-то текст'
//);
//$postsRepository->save($post);

//$post = $postsRepository->get(new UUID('12ee85c1-fc0f-431f-98be-550e83413dfd'));

//$comment = new Comment(
//    UUID::random(),
//    $post,
//    $user,
//    'Ещё какой-то комментарий к посту'
//);
//$commentRepository->save($comment);
//
//$comment = $commentRepository->get(new UUID('3b939069-61f0-40a0-adfc-4b9576627cc0'));
//print_r($comment);
//
//} catch (Exception $appException) {
//    echo "{$appException->getMessage()}";
//}



//$command = new CreateUserCommand($usersRepository);
//
//try {
//    $command->handle(Arguments::fromArgv($argv));
//} catch (AppException $appException) {
//    echo "{$appException->getMessage()}" . PHP_EOL;
//}