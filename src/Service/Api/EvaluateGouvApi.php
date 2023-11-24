<?php

namespace App\Service\Api;

use Exception;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EvaluateGouvApi
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * @throws TransportExceptionInterface
     */
    private function callApi($data): array
    {
        $url = $_ENV['EVALUATE_COMPANY_GOUV_API'];

        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode != 200) {
            throw new \Exception("Erreur lors de l'appel à l'API : statut $statusCode");
        }

        return json_decode($response->getContent(), true);
    }


    /**
     * @throws Exception
     */
    public function salaryNetPreTax(int $brut, string $contract): array
    {
        $data = array(
            'situation' => array(
                'salarié . contrat . salaire brut' => array(
                    "valeur" =>  $brut,
                    "unité" => "€ / mois"
                ),
                'salarié . contrat' => "'$contract'"
            ),
            'expressions' => array(
                "salarié . rémunération . net . à payer avant impôt",
                "salarié . cotisations . salarié",
                "salarié . coût total employeur"
            )
        );

        $json = self::callApi($data);

        return array(
            'netSalary' => $json['evaluate'][0]['nodeValue'],
            'employee_dues' => $json['evaluate'][1]['nodeValue'],
            'total_employer_cost' => $json['evaluate'][2]['nodeValue']);
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function gratificationMinimStage($net): array
    {
        $data = array(
            'situation' => array(
                'salarié . contrat . salaire brut' => array(
                    "valeur" =>  $net,
                    "unité" => "€ / mois"
                ),
                'salarié . contrat' => "'stage'"
            ),
            'expressions' => array(
                'salarié . contrat . stage . gratification minimale',
            )
        );

        $json = self::callApi($data);

        return array(
            'gratification' => $json['evaluate'][0]['nodeValue']
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function salaryNetPreTaxCddIndemnity(int $brut): array
    {
        $data = array(
            'situation' => array(
                'salarié . contrat . salaire brut' => array(
                    "valeur" =>  $brut,
                    "unité" => "€ / mois"
                ),
                'salarié . contrat' => "'stage'"
            ),
            'expressions' => array(
                "salarié . cotisations . salarié",
                "salarié . coût total employeur",
                "salarié . contrat . CDD . indemnité de fin de contrat"
            )
        );

        $json = self::callApi($data);

        return array(
            'employee_dues' => $json['evaluate'][0]['nodeValue'],
            'total_employer_cost' => $json['evaluate'][1]['nodeValue'],
            'severance_pay' => $json['evaluate'][2]['nodeValue'],

        );
    }
}