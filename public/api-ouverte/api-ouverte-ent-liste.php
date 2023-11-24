<?php


// Chargement des companies depuis le fichier companies.txt
$companies = loadEntreprises();


// Vérifie si la requête HTTP est un GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Méthode non autorisée
    die('Method Not Allowed');
}

// Récupére l'en-tête "Accept" pour déterminer le format de réponse souhaité
$acceptHeader = $_SERVER['HTTP_ACCEPT'];

// Vérifie le format demandé (JSON ou CSV)
if (str_contains($acceptHeader, 'application/json')) {
    // Envoyer la réponse en format JSON
    header('Content-Type: application/json');
    echo json_encode($companies, JSON_PRETTY_PRINT);

} elseif (str_contains($acceptHeader, 'text/csv')) {
    // Envoyer la réponse en format CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="companies.csv"');
    outputCSV($companies);
} else {
    // Format non pris en charge
    http_response_code(406); // Not Acceptable
    die('Not Acceptable');
}

// Fonction pour charger les companies depuis le fichier
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

// Fonction pour générer la sortie CSV
function outputCSV($data)
{
    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($data[array_key_first($data)])); // En-tête CSV

    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
}
