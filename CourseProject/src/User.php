<?php

namespace Mrlamer\CourseProject;
require_once 'vendor/autoload.php';

class User
{
    private int $id;


    public function __construct(
        private string $name,
        private string $surname
    )
    {}

    public function __toString(): string
    {
        return "$this->name $this->surname";
    }
}