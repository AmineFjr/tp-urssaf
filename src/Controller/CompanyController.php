<?php

namespace App\Controller;

use App\Service\Api\SearchCompanyApi;
use App\Service\StockJson;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/search-company', name: 'search_company_')]
#[OA\Tag(name: 'SearchCompany')]
class CompanyController extends AbstractController
{
    #[Route('', name: 'show', methods: ['GET'])]
    public function show(Request $request): Response
    {
        return $this->render('homepage/index.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }

    #[Route('', name: 'search', methods: ['POST'])]
    public function search(Request $request, SearchCompanyApi $searchCompanyApi): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $companyName = $data['companyName'];

            $response = $searchCompanyApi->searchCompany($companyName);

            return $this->json($response, Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/stock', name: 'stock', methods: ['POST'])]
    public function stock(Request $request, StockJson $stockJson): Response
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