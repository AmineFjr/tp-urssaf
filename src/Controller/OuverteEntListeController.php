<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use App\Service\StockJson;
use Exception;

#[Route('api-ouverte', name: 'ouverte_ent_liste_')]
#[OA\Tag(name: 'Ouverte Ent Liste')]
class OuverteEntListeController extends AbstractController
{
    #[Route('-ent-liste', name: '', methods: ['GET'])]
    public function browse(StockJson $stockJson): JsonResponse
    {
        try {
            return $this->json($stockJson->getAllDataFromTextFile(), Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('-ent-liste/{siren}', name: 'ouverte_ent_liste_by_siren', methods: ['GET'])]
    public function get(StockJson $stockJson, int $siren): JsonResponse
    {
        try {
            return $this->json($stockJson->getDataFromTextFile($siren), Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('-entreprise', name: '', methods: ['POST'])]
    public function create(Request $request, StockJson $stockJson): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $stockJson->sendDataToTextFile($data);

            return $this->json("les données ont été enregistrées avec success", Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}