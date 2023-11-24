<?php

namespace App\Controller;

use App\Service\Api\SearchCompanyApi;
use App\Service\StockJson;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'SearchCompany')]
class CompanyController extends AbstractController
{
    #[Route('api-search-company', name: 'search_company')]
    public function searchCompany(
        Request $request,
        SearchCompanyApi $searchCompanyApi,
        SessionInterface $session
    ): Response {
        try {

            // Lorsqu'une pagination est éffectuée sur l'entreprise chercher (Nous passons les paramètre à travers l'url)
            $search = $request->query->get('search') ?? '';
            $page = $request->query->get('page') ?? 1; // 1 => Page par defaut

            $response = $this->performSearch($searchCompanyApi, $session, $search, $page);

            return $this->render('company/companySearch.html.twig', [
                'response' => $response,
                'search' => $search,
                'page' => $page,
            ]);
        } catch (Exception $exception) {
            return $this->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    private function performSearch(
        SearchCompanyApi $searchCompanyApi,
        SessionInterface $session,
        string $search,
        int $page
    ): array {
        $response = [];

        if ($search !== '') {
            //Appel au service qui récupère l'entreprise rechercher
            $response = $searchCompanyApi->searchCompany($search, $page);

            // On récupère le contenu de la session
            $sessionCompanies = $session->get('companies') ?? [];

            foreach ($response['results'] as $company) {
                // Formattage des entreprises ([siren => companyData])
                $sessionCompanies[$company['siren']] = $company;
            }
            // Ajoute de toute les companies dans la sessions
            $session->set('companies', $sessionCompanies);

        }

        return $response;
    }
}
