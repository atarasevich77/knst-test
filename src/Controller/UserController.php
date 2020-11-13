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

        return $this->response($data);
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
            $data = $this->serializeErrors(Response::HTTP_NOT_FOUND, "User no valid");
            return $this->response($data, Response::HTTP_NOT_FOUND);
        } else {
            $data = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ];
            return $this->response($data);
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

            return $this->response($newUser->jsonSerialize());
        } catch (\Exception $e) {
            $data = $this->serializeErrors(Response::HTTP_UNPROCESSABLE_ENTITY, "Data no valid");
            return $this->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
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
            $data = $this->serializeErrors(Response::HTTP_NOT_FOUND, "User not found");
            return $this->response($data, Response::HTTP_NOT_FOUND);
        } else {
            $data = json_decode($request->getContent(), true);

            empty($data['firstName']) ? true : $user->setFirstName($data['firstName']);
            empty($data['lastName']) ? true : $user->setLastName($data['lastName']);
            empty($data['email']) ? true : $user->setEmail($data['email']);

            $updatedUser = $this->userRepository->updateUser($user);
            return $this->response($updatedUser->jsonSerialize());
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
            $data = $this->serializeErrors(Response::HTTP_NOT_FOUND, "User not found");
            return $this->response($data, Response::HTTP_NOT_FOUND);
        } else {
            $this->userRepository->removeUser($user);
            $data = $this->serializeErrors(Response::HTTP_OK, "User has been deleted");
            return $this->response($data);
        }
    }

    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function response(array $data, $status = Response::HTTP_OK, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @param $status
     * @param $errors
     * @return array
     */
    public function serializeErrors($status, $errors): array
    {
        return [
            'status' => $status,
            'errors' => $errors,
        ];
    }
}