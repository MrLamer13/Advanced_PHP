<?php

namespace GeekBrains\LevelTwo\Http\Actions\PostsLikes;

use GeekBrains\LevelTwo\Blog\Repositories\PostsLikesRepository\PostsLikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Exceptions\HttpException;
use GeekBrains\LevelTwo\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Exceptions\PostLikesNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class FindPostLikes implements ActionInterface
{
    public function __construct(
        private PostsLikesRepositoryInterface $postsLikesRepository
    )
    {    }

    public function handle(Request $request): Response
    {
        try {
            $postUuid = $request->query('post_uuid');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $postLikes = $this->postsLikesRepository->getByPostUuid(new UUID("$postUuid"));
        } catch (PostLikesNotFoundException | InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $result = [];
        foreach ($postLikes as $postLike) {
            $post = (string)$postLike->post()->uuid();
            $result["post: $post"][] = [
                'uuid' => (string)$postLike->uuid(),
                'author' => $postLike->author()->username()
            ];
        }

        return new SuccessfulResponse($result);

    }
}