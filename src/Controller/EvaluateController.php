<?php

namespace App\Controller;

use App\Form\SalaryType;
use App\Service\Api\EvaluateGouvApi;
use App\Service\StockJson;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class EvaluateController extends AbstractController
{
    /**
     * @throws \Exception
     * @throws TransportExceptionInterface
     */
    #[Route('selectedCompany', name:"selectedCompany")]
    public function selectedCompany(Request $request, SessionInterface $session, StockJson $stockJson, EvaluateGouvApi $evaluate): Response {

        // A la selection d'une entreprise on récupère l'id (siren)
        $companySiren = $request->query->get('id') ?? '';

        // Récupération des entreprises depuis la session
        $sessionCompanies = $session->get('companies');

        // Recupération des informations de l'entreprise sélectionnée
        $company = $sessionCompanies[$companySiren];

        // Grace au service sendDataToTextFile on ajout l'entreprise dans le fichier
        $stockJson->sendDataToTextFile($company);

        $salaryForm = $this->createForm(SalaryType::class);

        $salaryForm->handleRequest($request);

        if ($salaryForm->isSubmitted()) {
            $salary = $salaryForm->getData()['salary'];
            $contract = $salaryForm->getData()['contract'];


            if ($contract === 'CDI' || $contract === 'apprentissage') {
                $response = $evaluate->salaryNetPreTax(intval($salary), $contract);
            } elseif ($contract === 'stage') {
                $response = $evaluate->gratificationMinimStage($salary);
            } elseif ($contract === 'CDD') {
                $response = $evaluate->salaryNetPreTaxCddIndemnity($salary);
            }
        }

        if ($companySiren) {
            $this->addFlash('success', 'Votre entreprise a été ajoutée dans le fichier');
        }

        return $this->render('salary/salary.html.twig', [
            'companyData' => $company,
            'salaryForm' => $salaryForm->createView(),
            'response' => $response ?? [],
            'contract' => $contract ?? '',
        ]);
    }
}
