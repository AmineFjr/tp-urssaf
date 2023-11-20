<?php

namespace App\Service\Api;

use Exception;

class EvaluateGouvApi
{
    /**
     * @throws Exception
     */
    private function callApi($data): array
    {
        $headers = array(
            'Content-Type: application/json'
        );

        $ch = curl_init($_ENV['EVALUATE_GOUV_URL']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $json = json_decode($response, true);

        if (isset($json['situationError'])) {
            throw new Exception($json['situationError']['message']);
        }

        return $json;
    }

    /**
     * @throws Exception
     */
    public function salaireNetEnBrutMensuel($net): array
    {
        $data = array(
            'situation' => array(
                'salarié . rémunération . net . payé après impôt' => $net . ' €'
            ),
            'expressions' => array(
                'salarié . contrat . salaire brut',
                'salarié . coût total employeur'
            )
        );

        $json = self::callApi($data);

        return array($json['evaluate'][0]['nodeValue'], $json['evaluate'][1]['nodeValue']);
    }

    /**
     * @throws Exception
     */
    public function salaireNetAvantImpot(int $brut): array
    {
        $data = array(
            'situation' => array(
                'salarié . contrat . salaire brut' => array(
                    "valeur" =>  $brut,
                    "unité" => "€ / mois"
                ),
                'salarié . contrat' => "'CDI'"
            ),
            'expressions' => array(
                'salarié . coût total employeur',
                'salarié . cotisations . salarié'
            )
        );

        $json = self::callApi($data);

        return array($json['evaluate'][0]['nodeValue'], $json['evaluate'][1]['nodeValue']);
    }

    /**
     * @throws Exception
     */
    public function gratificationMinimStage($net): array
    {
        $data = array(
            'situation' => array(
                'salarié . contrat . salaire brut' => array(
                    "valeur" =>  $net,
                    "unité" => "€ / mois"
                ),
                'salarié . contrat' => "'CDI'"
            ),
            'expressions' => array(
                'salarié . contrat . stage . gratification minimale',
            )
        );

        $json = self::callApi($data);

        return array($json['evaluate'][0]['nodeValue'], $json['evaluate'][1]['nodeValue']);
    }

    /**
     * @throws Exception
     */
    public function salaireAlternant($net): array
    {
        $data = array(
            'situation' => array(
                'salarié . contrat . salaire brut' => array(
                    "valeur" =>  $net,
                    "unité" => "€ / mois"
                ),
                'salarié . contrat' => "'alternance'"
            ),
            'expressions' => array(
                'salarié . coût total employeur',
                'salarié . cotisations . salarié'
            )
        );

        $json = self::callApi($data);

        return array($json['evaluate'][0]['nodeValue'], $json['evaluate'][1]['nodeValue']);
    }

    /**
     * @throws Exception
     */
    public function salaireCDD(string $brut): array
    {
        $data = array(
            'situation' => array(
                'salarié . contrat . salaire brut' => array(
                    "valeur" =>  $brut,
                    "unité" => "€ / mois"
                ),
                'salarié . contrat' => "'CDD'",
                'salarié . contrat . CDD . durée' => array(
                    "valeur" =>  19,
                    "unité" => "mois"
                ),
            ),
            'expressions' => array(
                'salarié . coût total employeur',
                'salarié . cotisations . salarié',
                "salarié . contrat . CDD . indemnité de fin de contrat",
            )
        );

        $json = self::callApi($data);

        return array($json['evaluate'][0]['nodeValue'], $json['evaluate'][1]['nodeValue']);
    }

}