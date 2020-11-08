<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController
{
    private UserRepository $userRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route ("/users/", name="get_or_create_user", methods={"POST"})
     */
    public function get_or_create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['email'];

        if (empty($firstName) || empty($lastName) || empty($email)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user instanceof User) {
            $data = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ];

            return new JsonResponse($data, Response::HTTP_OK);

        } else {
            $this->userRepository->saveUser($firstName, $lastName, $email);

            return new JsonResponse(['status' => 'User created!'], Response::HTTP_CREATED);
        }
    }


}