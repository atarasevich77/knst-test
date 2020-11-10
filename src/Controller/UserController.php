<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/api", name="users_api")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    /**
     * @return JsonResponse
     * @Route ("/users", name="users", methods={"GET"})
     */
    public function getUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @Route ("/users/{id}", name="users_get", methods={"GET"})
     */
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if(!$user) {
            $data = [
                'errors' => 404,
                'status' => "User no valid",
            ];
            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        } else {
            $data = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ];
            return new JsonResponse($data, Response::HTTP_OK);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route ("/users", name="users_post", methods={"POST"})
     */
    public function addUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $firstName = $data['firstName'];
            $lastName = $data['lastName'];
            $email = $data['email'];

            if (empty($firstName) || empty($lastName) || empty($email)) {
                throw new NotFoundHttpException('Expecting mandatory parameters!');
            }
            $newUser = $this->userRepository->createUser($firstName, $lastName, $email);

            return new JsonResponse($newUser->jsonSerialize(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return new JsonResponse($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @Route ("/users/{id}", name="users_put", methods={"PUT"})
     */
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if(!$user) {
            $data = [
                'status' => 404,
                'errors' => "User not found",
            ];
            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        } else {
            $data = json_decode($request->getContent(), true);

            empty($data['firstName']) ? true : $user->setFirstName($data['firstName']);
            empty($data['lastName']) ? true : $user->setLastName($data['lastName']);
            empty($data['email']) ? true : $user->setEmail($data['email']);

            $updatedUser = $this->userRepository->updateUser($user);
            return new JsonResponse($updatedUser->jsonSerialize(), Response::HTTP_OK);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @Route ("/users/{id}", name="users_delete", methods={"DELETE"})
     */
    public function delete(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if(!$user) {
            $data = [
                'status' => 404,
                'errors' => "User not found",
            ];
            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        } else {
            $this->userRepository->removeUser($user);
            $data = [
                'status' => 204,
                'errors' => "User has been deleted",
            ];
            return new JsonResponse($data, Response::HTTP_NO_CONTENT);
        }
    }
}