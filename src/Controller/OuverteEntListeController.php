<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use App\Service\StockJson;
use Exception;

#[Route('api-ouverte', name: 'Ouverte Ent Liste')]
#[OA\Tag(name: 'Ouverte Ent Liste')]
class OuverteEntListeController extends AbstractController
{
    #[Route('-ent-liste', name: 'ouverte_ent_liste', methods: ['GET'])]
    public function browse(Request $request, StockJson $stockJson): JsonResponse
    {
        try {
            if ($request->getMethod() !== 'GET') {
                throw new MethodNotAllowedHttpException(['GET'], 'Method not allowed');
            }

            $supportedFormats = ['html', 'json'];
            $format = $request->getRequestFormat();

            if (!in_array($format, $supportedFormats)) {
                throw new NotAcceptableHttpException('Format not supported');
            }

            $data = $stockJson->getAllDataFromTextFile();
            if (empty($data)) {
                return $this->json("Aucune entreprise n'est enregistrée", Response::HTTP_OK);
            }
            return $this->json(["Liste des entreprises présentes" => $stockJson->getAllDataFromTextFile()], Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('-ent-liste/{siren}', name: 'ouverte_ent_liste_by_siren', methods: ['GET'])]
    public function get(Request $request, StockJson $stockJson, int $siren): JsonResponse
    {
        try {
            if ($request->getMethod() !== 'GET') {
                throw new MethodNotAllowedHttpException(['GET'], 'Method not allowed');
            }
            $data = $stockJson->getAllDataFromTextFile();
            if (empty($data)) {
                return $this->json("Aucune entreprise avec ce SIREN", Response::HTTP_NOT_FOUND);
            }
            return $this->json($stockJson->getAllDataFromTextFile(), Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('-entreprise', name: '', methods: ['POST'])]
    public function create(Request $request, StockJson $stockJson): JsonResponse
    {
        try {

            if ($request->getMethod() !== 'POST') {
                throw new MethodNotAllowedHttpException(['POST'], 'Method not allowed');
            }

            $supportedFormats = ['html', 'json'];
            $format = $request->getRequestFormat();

            if (!in_array($format, $supportedFormats)) {
                throw new NotAcceptableHttpException('Format JSON invalide ou donnée manquante');
            }

            $data = json_decode($request->getContent(), true);

            if ($stockJson->fileExist($data["siren"])) {
                return $this->json("Entreprise déjà enregistrée", Response::HTTP_BAD_REQUEST);
            }

            $stockJson->sendDataToTextFile($data);

            return $this->json("Entreprise créée", Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}