<?php

namespace Mrlamer\CourseProject;

class Comment
{
    private int $id;
    private int $idAuthor;
    private int $idArticle;


    public function __construct(
        private string $text
    )
    {
    }

    public function __toString(): string
    {
        return "$this->text";
    }
}