<?php

namespace App\Service\Api;

class SearchCompanyApi
{
    public function searchCompany(string $companyName)
    {
        $gouvApi = $_ENV['SEARCH_COMPANY_GOUV_API'] . '?q=' . $companyName;
        $response = file_get_contents($gouvApi);
        $result = json_decode($response, true);
        return $result["results"];
    }
}