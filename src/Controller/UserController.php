<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1')]
class UserController extends AbstractController
{
    private static array $cachedUsers = [];
    private const JSON_PATH = "../public/users.json";
    public function __construct()
    {
        $jsonData = file_get_contents(self::JSON_PATH);
        self::$cachedUsers = json_decode($jsonData, true);
    }

    #[Route('/users', name: 'app_get-all-users', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function getAllUsers(): JsonResponse
    {
        return new JsonResponse([
            'data' => self::$cachedUsers
        ], Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'app_get-user-by-id', methods: ['GET'])]
    public function getUserById(string $id): JsonResponse
    {
        $userData = $this->findUserById($id);

        return new JsonResponse([
            'data' => $userData
        ], Response::HTTP_OK);
    }

    #[Route('/users', name: 'app_create-user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['email'], $requestData['name'])) {
            throw new UnprocessableEntityHttpException("name and email are required");
        }

        $newUser = [
            'id'    => $this->getGUID(),
            'name'  => $requestData['name'],
            'email' => $requestData['email']
        ];

        self::$cachedUsers[] = $newUser;
        file_put_contents(self::JSON_PATH, json_encode(self::$cachedUsers, JSON_PRETTY_PRINT));

        return new JsonResponse([
            'data' => $newUser
        ], Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'app_delete-user', methods: ['DELETE'])]
    public function deleteUser(string $id): JsonResponse
    {
        $user = $this->findUserById($id);
        $userIndex = array_search($user, self::$cachedUsers);

        if ($userIndex === false) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        unset(self::$cachedUsers[$userIndex]);
        file_put_contents(self::JSON_PATH, json_encode(self::$cachedUsers, JSON_PRETTY_PRINT));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    #[Route('/users/{id}', name: 'app_update-user', methods: ['PATCH'])]
    public function updateUser(string $id, Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['name']) && !isset($requestData['email'])) {
            throw new UnprocessableEntityHttpException("name or email is required");
        }

        $user = $this->findUserById($id);
        $userIndex = array_search($user, self::$cachedUsers);

        if ($userIndex === false) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if (isset($requestData['name'])) {
            self::$cachedUsers[$userIndex]['name'] = $requestData['name'];
        }

        if (isset($requestData['email'])) {
            self::$cachedUsers[$userIndex]['email'] = $requestData['email'];
        }

        file_put_contents(self::JSON_PATH, json_encode(self::$cachedUsers, JSON_PRETTY_PRINT));

        return new JsonResponse([
            'data' => self::$cachedUsers[$userIndex]
        ], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return string[]
     */
    public function findUserById(string $id): array
    {
        $userData = null;

        foreach (self::$cachedUsers as $user) {
            if (!isset($user['id'])) {
                continue;
            }

            if ($user['id'] == $id) {
                $userData = $user;
                break;
            }

        }

        if (!$userData) {
            throw new NotFoundHttpException("User with id " . $id . " not found");
        }

        return $userData;
    }
    function getGUID(){
        mt_srand((double)microtime()*10000);
        $charid = md5(uniqid(rand(), true));
        $hyphen = chr(45);
        $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
        return $uuid;
    }
}
