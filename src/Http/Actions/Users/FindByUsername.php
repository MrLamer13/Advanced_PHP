<?php

namespace GeekBrains\LevelTwo\Http\Actions\Users;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Exceptions\HttpException;
use GeekBrains\LevelTwo\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;

class FindByUsername implements ActionInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    )
    {    }

    public function handle(Request $request): Response
    {
        try {
            $username = $request->query('username');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $user = $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $exception) {

            return new ErrorResponse($exception->getMessage());
        }


        return new SuccessfulResponse([
            'username' => $user->username(),
            'name' => $user->name()->first() . ' ' . $user->name()->last(),
        ]);
    }
}