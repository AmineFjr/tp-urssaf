<?php

namespace App\Controller;

use App\Service\SecurityService;
use App\Service\StockJson;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
#[Route('api-protege', name: 'protege_')]
#[OA\Tag(name: 'Protege')]
class ProtegeController extends AbstractController
{
    #[Route('/{siren}', name: 'index', methods: ['DELETE'])]
    public function delete(Request $request, StockJson $stockJson, int $siren, SecurityService $securityService): Response
    {
        try {
            if ($request->getMethod() !== 'DELETE') {
                throw new MethodNotAllowedHttpException(['DELETE'], 'Method not allowed');
            }

            if (!$securityService->login($request)) {
                return new JsonResponse("Vous n'êtes pas connectés", Response::HTTP_UNAUTHORIZED);
            }
            $supportedFormats = ['html', 'json'];
            $format = $request->getRequestFormat();

            if (!in_array($format, $supportedFormats)) {
                throw new NotAcceptableHttpException('Format not supported');
            }

            if (empty($siren)) {
                return $this->json("Aucune entreprise avec ce SIREN", Response::HTTP_NOT_FOUND);
            }

            $stockJson->deleteDataFromTextFile($siren);
            return $this->json("Entreprise supprimée", Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{siren}', name: 'update', methods: ['PATCH'])]
    public function update(Request $request, StockJson $stockJson, int $siren, SecurityService $securityService): Response
    {
        try {
            if (!$securityService->login($request)) {
                return new JsonResponse("Vous n'êtes pas connectés", Response::HTTP_UNAUTHORIZED);
            }

            if ($request->getMethod() !== 'PATCH') {
                throw new MethodNotAllowedHttpException(['PATCH'], 'Method not allowed');
            }

            $supportedFormats = ['html', 'json'];
            $format = $request->getRequestFormat();

            if (!in_array($format, $supportedFormats)) {
                throw new NotAcceptableHttpException('Format not supported');
            }

            $stockJson->deleteDataFromTextFile($siren);
            $data = json_decode($request->getContent(), true);
            $stockJson->updateDataFromTextFile($data, $siren);

            return $this->json("Entreprise modifiée", Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}