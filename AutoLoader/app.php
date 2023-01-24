<?php

use Geek_Brains\Blog\Post;
use Geek_Brains\Person\Name;
use Geek_Brains\Person\Person;

spl_autoload_register(function ($class) {

    $posEndSlash = strrpos($class, '\\');
    $namespace = substr($class, 0, $posEndSlash);
    $className = substr($class, $posEndSlash + 1);
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR .
        str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

//    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }

        echo $file . PHP_EOL;

});

$class = new Post(
    new Person(
    new Name('Иван', 'Никитин'),
    new DateTimeImmutable()
),
    'Всем привет!'
);