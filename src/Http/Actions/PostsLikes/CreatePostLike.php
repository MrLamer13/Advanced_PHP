<?php

namespace GeekBrains\LevelTwo\Http\Actions\PostsLikes;

use GeekBrains\LevelTwo\Blog\PostLike;
use GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository\PostsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\AuthException;
use GeekBrains\LevelTwo\Exceptions\HttpException;
use GeekBrains\LevelTwo\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Exceptions\PostLikeExistException;
use GeekBrains\LevelTwo\Exceptions\PostLikesNotFoundException;
use GeekBrains\LevelTwo\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class CreatePostLike implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface      $postsRepository,
        private TokenAuthenticationInterface  $authentication,
        private PostsLikesRepositoryInterface $postsLikesRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $author = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $postUuid = new UUID($request->jsonBodyField('post_uuid'));
        } catch (HttpException|InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $post = $this->postsRepository->get($postUuid);
        } catch (PostNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $newPostLikeUuid = UUID::random();

        try {
            $postLike = new PostLike(
                $newPostLikeUuid,
                $post,
                $author
            );
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        if ($this->likeExists($postUuid, $author)) {
            throw new PostLikeExistException("Лайк уже поставлен");
        }

        $this->postsLikesRepository->save($postLike);

        return new SuccessfulResponse([
            'uuid' => (string)$newPostLikeUuid
        ]);

    }

    private function likeExists(UUID $postUuid, User $user): bool
    {
        try {
            $postLikes = $this->postsLikesRepository->getByPostUuid($postUuid);
        } catch (PostLikesNotFoundException) {
            return false;
        }

        foreach ($postLikes as $postLike) {
            if ($postLike->author() == $user) {
                return true;
            }
        }

        return false;

    }
}