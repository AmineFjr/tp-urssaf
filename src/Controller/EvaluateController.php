<?php

namespace App\Controller;

use App\Service\Api\EvaluateGouvApi;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('api-evaluate', name: 'evaluate_')]
#[OA\Tag(name: 'Evaluate')]
class EvaluateController extends AbstractController
{
    #[Route('', name: 'evaluate', methods: ['POST'])]
    public function index(Request $request, EvaluateGouvApi $evaluateCompanyApi): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $net = $data['net'];

            $response = $evaluateCompanyApi->salaireNetAvantImpot($net);

            return $this->json($response, Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/calcul-stage', name: 'calcul_stage', methods: ['POST'])]
    public function calculStage(Request $request, EvaluateGouvApi $evaluateCompanyApi): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $brut = $data['brut'];

            $response = $evaluateCompanyApi->salaireCDD($brut);

            return $this->json($response, Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}