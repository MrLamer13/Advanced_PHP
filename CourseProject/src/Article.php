<?php

namespace Mrlamer\CourseProject;

class Article
{
    private int $id;
    private int $idAuthor;


    public function __construct(
    private string $header,
    private string $text
    )
    {
    }

    public function __toString(): string
    {
        return "$this->header >>> $this->text";
    }
}