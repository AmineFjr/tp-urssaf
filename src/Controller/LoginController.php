<?php

namespace App\Controller;

use App\Service\SecurityService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('api-login', name: 'login')]
#[OA\Tag(name: 'Login')]
class LoginController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function login(Request $request, SecurityService $securityService): JsonResponse
    {
        try {
            if (!$securityService->login($request)) {
                return new JsonResponse("Vous n'êtes pas connectés", Response::HTTP_UNAUTHORIZED);
            }
            return $this->json("Vous êtes connectés", Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

}