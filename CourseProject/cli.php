<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mrlamer\CourseProject\Article;
use Mrlamer\CourseProject\Comment;
use Mrlamer\CourseProject\User;

$faker = Faker\Factory::create();

if ( isset($argv[1])   ) {
    switch ( $argv[1] ) {
        case 'name':
            $user = new User($faker->firstName, $faker->lastName);
            echo $user;
            break;
        case 'post':
            $post = new Article($faker->realText(50), $faker->realText(300));
            echo $post;
            break;
        case 'comment':
            $comment = new Comment($faker->realText(100));
            echo $comment;
            break;
    }

}
