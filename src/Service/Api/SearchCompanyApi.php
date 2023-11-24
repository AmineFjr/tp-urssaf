<?php

namespace App\Service\Api;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SearchCompanyApi
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function searchCompany(string $companyName, int $page = 1): array | string
    {
        try {
            $response = $this->client->request(
                'GET',
                $_ENV['SEARCH_COMPANY_GOUV_API'] . '?q=' . $companyName. '&page='. $page,
            );
            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                throw new \Exception("Erreur lors de l'appel à l'API : statut $statusCode");
            }
            return json_decode($response->getContent(), true);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return $e->getMessage();
        }
    }
}