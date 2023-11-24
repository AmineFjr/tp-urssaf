<?php


// Chargement des companies depuis le fichier
$companies = loadEntreprises();


// Vérifier si la requête HTTP est un GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Méthode non autorisée
    die('Method Not Allowed');
}

// Récupérer l'en-tête "Accept" pour déterminer le format de réponse souhaité
$acceptHeader = $_SERVER['HTTP_ACCEPT'];

// Vérifier le format demandé JSON
if (str_contains($acceptHeader, 'application/json')) {
    // Envoyer la réponse en format JSON
    header('Content-Type: application/json');

    if (!isset($_GET['siren'])) {
        http_response_code(400); // Méthode non autorisée
        die('Bad request');
    }

    $siren = $_GET['siren'];

    if (isset($companies[$siren])) {
        // Entreprise trouvée, renvoyer les informations au format JSON
        http_response_code(200);
        echo json_encode($companies[$siren]);
        exit;
    } else {
        // Aucune entreprise avec ce SIREN, renvoyer une réponse 404
        http_response_code(404);
        echo json_encode(['error' => 'Company not found']);
    }

} else {
    // Format non pris en charge
    http_response_code(406); // Not Acceptable
    die('Not Acceptable');
}

function loadEntreprises()
{
    $filename = '../txt/companies.txt';

    if (!file_exists($filename)) {
        return [];
    }

    $content = file_get_contents($filename);
    $companies = json_decode($content, true);

    return $companies ?: [];
}
