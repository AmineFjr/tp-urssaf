<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyFormType;
use App\Service\Api\SearchCompanyApi;
use App\Service\StockJson;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/search-company', name: 'search_company_')]
#[OA\Tag(name: 'SearchCompany')]
class CompanyController extends AbstractController
{
    public array $responses = [];

    #[Route('', name: 'show')]
    public function show(Request $request, SearchCompanyApi $searchCompanyApi, StockJson $stockJson, SessionInterface $session): Response
    {
        try {
            $company = new Company();
            $form = $this->createForm(CompanyFormType::class, $company);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->responses = $searchCompanyApi->searchCompany($company->getNomComplet(), $company->getSiren());
            }

            $session->set('entrepriseResultats', $this->responses);

            return $this->render('homepage/index.html.twig', [
                'company_form' => $form->createView(),
                'response' => $this->responses,
            ]);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    public function search(Request $request, SearchCompanyApi $searchCompanyApi): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $companyName = $data['companyName'];
            $siren = $data['siren'];

            $response = $searchCompanyApi->searchCompany($companyName, $siren);

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

            return $this->json("les données ont été enregistrées avecz success", Response::HTTP_OK);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/ajax', name: 'ajax', methods: ['POST'])]
    public function ajaxAction(Request $request, SessionInterface $session, StockJson $stockJson): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $responseData = [
                 ...$data,
            ];

            $entreprise = $this->searchCompanyBySiren($responseData['siren'], $session->get('entrepriseResultats'));

            $stockJson->sendDataToTextFile($entreprise);

            if ($entreprise) {
                return $this->json($entreprise);
            } else {
                return $this->json($this->responses, 404);
            }
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        }
    }

    public function searchCompanyBySiren($siren, $entreprises)
    {
        foreach ($entreprises as $response) {
            if (isset($response['siren']) && $response['siren'] == $siren) {
                return $response;
            }
        }

        return null;
    }


}