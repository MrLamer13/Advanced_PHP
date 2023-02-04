<?php

use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\UUID;


require_once __DIR__ . '/vendor/autoload.php';

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$usersRepository = new SqliteUsersRepository($connection);
// $usersRepository = new InMemoryUsersRepository();
$postsRepository = new SqlitePostsRepository($connection);
$commentRepository = new SqliteCommentsRepository($connection);

try {

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

$comment = $commentRepository->get(new UUID('3b939069-61f0-40a0-adfc-4b9576627cc0'));
print_r($comment);

} catch (Exception $appException) {
    echo "{$appException->getMessage()}";
}



//$command = new CreateUserCommand($usersRepository);
//
//try {
//    $command->handle(Arguments::fromArgv($argv));
//} catch (AppException $appException) {
//    echo "{$appException->getMessage()}" . PHP_EOL;
//}