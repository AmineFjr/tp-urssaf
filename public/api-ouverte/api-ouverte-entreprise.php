<?php

header('Content-Type: application/json');

// Chemin vers le fichier de stockage des entreprises

$filename = '../txt/companies.txt';

// Vérifier que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer le corps de la requête en tant que chaîne JSON
    $jsonInput = file_get_contents('php://input');

    // Vérifier si le JSON est valide
    $jsonData = json_decode($jsonInput, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Valider les données requises
        if (
            strlen($jsonData['siren']) === 9
            && !empty($jsonData['raison_sociale'])
            && is_numeric($jsonData['adresse']['num'])
            && !empty($jsonData['adresse']['voie'])
            && is_numeric($jsonData['adresse']['code_postale'])
            && strlen($jsonData['adresse']['code_postale']) === 5
            && !empty($jsonData['adresse']['ville'])
            && is_numeric($jsonData['adresse']['gps']['latitude'])
            && is_numeric($jsonData['adresse']['gps']['longitude'])
        ) {
            // Charger les entreprises existantes depuis le fichier
            $existingCompanies = [];
            if (file_exists($filename)) {
                $existingCompanies = json_decode(file_get_contents($filename), true);
            }

            // Vérifier si l'entreprise existe déjà
            if (isset($existingCompanies[$jsonData['siren']])) {
                http_response_code(409); // Entreprise existe déjà
                echo json_encode(['message' => 'L\'entreprise existe déjà.'], JSON_PRETTY_PRINT);
            } else {
                // Ajouter la nouvelle entreprise
                $existingCompanies[$jsonData['siren']] = [
                    'siren' => $jsonData['siren'],
                    'raison_sociale' => $jsonData['raison_sociale'],
                    'adresse' => [
                        'num' => $jsonData['adresse']['num'],
                        'voie' => $jsonData['adresse']['voie'],
                        'code_postale' => $jsonData['adresse']['code_postale'],
                        'ville' => $jsonData['adresse']['ville'],
                        'gps' => [
                            'latitude' => $jsonData['adresse']['gps']['latitude'],
                            'longitude' => $jsonData['adresse']['gps']['longitude']
                        ]
                    ]
                ];

                // Enregistrer les entreprises mises à jour dans le fichier
                file_put_contents($filename, json_encode($existingCompanies, JSON_PRETTY_PRINT));

                http_response_code(201); // Entreprise créée
                echo json_encode(['message' => 'L\'entreprise a été créée avec succès.', 'url' => 'https://127.0.0.1:8000/api-ouverte/api-ouverte-ent.php?siren='.$jsonData['siren']], JSON_PRETTY_PRINT);
                exit();
            }
        } else {
            http_response_code(400); // JSON invalide ou données manquantes
            echo json_encode(['message' => 'Le format du JSON est invalide ou des données sont manquantes.'], JSON_PRETTY_PRINT);
            exit();
        }
    } else {
        http_response_code(400); // JSON invalide
        echo json_encode(['message' => 'Le format du JSON est invalide.'], JSON_PRETTY_PRINT);
        exit();
    }
} else {
    http_response_code(405); // Méthode non autorisée
    echo json_encode(['message' => 'La méthode HTTP utilisée n\'est pas autorisée.'], JSON_PRETTY_PRINT);
    exit();
}

