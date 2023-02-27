<?php

namespace GeekBrains\LevelTwo\Http\Actions\CommentsLikes;

use GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository\CommentsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentLikesNotFoundException;
use GeekBrains\LevelTwo\Exceptions\HttpException;
use GeekBrains\LevelTwo\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class FindCommentLikes implements ActionInterface
{
    public function __construct(
        private CommentsLikesRepositoryInterface $commentsLikesRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $commentUuid = $request->query('comment_uuid');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $commentLikes = $this->commentsLikesRepository->getByCommentUuid(new UUID("$commentUuid"));
        } catch (CommentLikesNotFoundException|InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $result = [];
        foreach ($commentLikes as $commentLike) {
            $comment = (string)$commentLike->comment()->uuid();
            $result["comment: $comment"][] = [
                'uuid' => (string)$commentLike->uuid(),
                'author' => $commentLike->author()->username()
            ];
        }

        return new SuccessfulResponse($result);
    }
}