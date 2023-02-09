<?php

namespace GeekBrains\LevelTwo\Http\Actions\CommentsLikes;

use GeekBrains\LevelTwo\Blog\CommentLike;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsLikesRepository\CommentsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\CommentLikeExistException;
use GeekBrains\LevelTwo\Exceptions\CommentLikesNotFoundException;
use GeekBrains\LevelTwo\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Exceptions\HttpException;
use GeekBrains\LevelTwo\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\IdentificationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class CreateCommentLike implements ActionInterface
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository,
        private IdentificationInterface $identification,
        private CommentsLikesRepositoryInterface $commentsLikesRepository
    )
    {    }

    public function handle(Request $request): Response
    {
        $author = $this->identification->user($request);

        try {
            $commentUuid = new UUID($request->jsonBodyField('comment_uuid'));
        } catch (HttpException | InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $comment = $this->commentsRepository->get($commentUuid);
        } catch (CommentNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $newCommentLikeUuid = UUID::random();

        try {
            $commentLike = new CommentLike(
                $newCommentLikeUuid,
                $comment,
                $author
            );
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        if ($this->likeExists($commentUuid, $author)) {
            throw new CommentLikeExistException("Лайк уже поставлен");
        }

        $this->commentsLikesRepository->save($commentLike);

        return new SuccessfulResponse([
            'uuid' => (string)$newCommentLikeUuid
        ]);

    }

    private function likeExists(UUID $commentUuid, User $user): bool
    {
        try {
            $commentLikes = $this->commentsLikesRepository->getByCommentUuid($commentUuid);
        } catch (CommentLikesNotFoundException) {
            return false;
        }

        foreach ($commentLikes as $commentLike) {
            if ($commentLike->author() == $user) {
                return true;
            }
        }

        return false;
    }
}