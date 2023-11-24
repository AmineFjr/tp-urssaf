<?php

header('Content-Type: application/json');


if (apache_request_headers()['Authorization'] ?? false) {
    $authHeader = apache_request_headers()['Authorization'];
} else {
    http_response_code(401);
    echo json_encode(['message' => 'Veuillez vous auhentifiez '], JSON_PRETTY_PRINT);
    exit();
}

$validUsers = [
    'user' => password_hash('kiol0560456', PASSWORD_BCRYPT)
];

if (authenticate($validUsers, $authHeader)) {
    // Votre système d'authentification (à mettre en place)

    // Vérifier la méthode HTTP
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'DELETE':
            handleDeleteRequest();
            break;
        case 'PATCH':
            handlePatchRequest();
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'La méthode HTTP utilisée n\'est pas autorisée.'], JSON_PRETTY_PRINT);
            break;
    }
}

function authenticate(array $validUsers, string $authHeader): bool
{
    if (!isset($authHeader)) {
        header('WWW-Authenticate: Basic realm="Restricted Area"');
        http_response_code(401);
        echo json_encode(["erreur" => "Autorisation requise"], JSON_PRETTY_PRINT);
        return false;
    }

    list($type, $credentials) = explode(' ', $authHeader, 2);
    list($username, $password) = explode(':', base64_decode($credentials), 2);

    if (!isset($validUsers[$username]) || !password_verify($password, $validUsers[$username])) {
        http_response_code(401);
        echo json_encode(["erreur" => "Login ou mot de passe invalide"], JSON_PRETTY_PRINT);
        return false;
    }

    return true;
}

function handleDeleteRequest()
{
    // Récupérer le corps de la requête en tant que chaîne JSON
    $jsonInput = file_get_contents('php://input');

    // Vérifier si le JSON est valide
    $jsonData = json_decode($jsonInput, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Valider les données requises
        if (isset($jsonData['siren'])) {
            // Charger les entreprises existantes depuis le fichier
            $filename = '../txt/companies.txt';
            if (file_exists($filename)) {
                $existingCompany = json_decode(file_get_contents($filename), true);

                // Vérifier si l'entreprise existe
                if (isset($existingCompany[$jsonData['siren']])) {
                    // Supprimer l'entreprise du fichier
                    unset($existingCompany[$jsonData['siren']]);
                    file_put_contents($filename, json_encode($existingCompany, JSON_PRETTY_PRINT));

                    http_response_code(200); // Entreprise supprimée
                    echo json_encode(['message' => 'L\'entreprise a été supprimée avec succès.'], JSON_PRETTY_PRINT);
                } else {
                    http_response_code(404); // Aucune entreprise avec ce siren
                    echo json_encode(['message' => 'Aucune entreprise avec ce siren.'], JSON_PRETTY_PRINT);
                }
            } else {
                http_response_code(404); // Aucune entreprise trouvée
                echo json_encode(['message' => 'Aucune entreprise trouvée.'], JSON_PRETTY_PRINT);
            }
        } else {
            http_response_code(400); // JSON invalide ou données manquantes
            echo json_encode(['message' => 'Le format du JSON est invalide ou des données sont manquantes.'], JSON_PRETTY_PRINT);
        }
    } else {
        http_response_code(400); // JSON invalide
        echo json_encode(['message' => 'Le format du JSON est invalide.'], JSON_PRETTY_PRINT);
    }
}

function handlePatchRequest()
{
    // Récupérer le corps de la requête en tant que chaîne JSON
    $jsonInput = file_get_contents('php://input');

    // Vérifier si le JSON est valide
    $jsonData = json_decode($jsonInput, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Valider les données requises
        if (isset($jsonData['Siren'])) {
            // Charger les entreprises existantes depuis le fichier
            $filename = '../txt/companies.txt';
            if (file_exists($filename)) {
                $existingCompany = json_decode(file_get_contents($filename), true);

                // Vérifier si l'entreprise existe
                if (isset($existingCompany[$jsonData['Siren']])) {
                    // Mettre à jour les données de l'entreprise
                    foreach ($jsonData as $key => $value) {
                        if ($key !== 'Siren') {
                            // Vérifier si le champ est "adresse"
                            if ($key === 'adresse') {
                                // Si c'est "adresse", traiter chaque sous-champ individuellement
                                foreach ($value as $addressKey => $addressValue) {
                                    $existingCompany[$jsonData['Siren']]['adresse'][$addressKey] = $addressValue;
                                }
                            } else {
                                $existingCompany[$jsonData['Siren']][$key] = $value;
                            }
                        }
                    }

                    // Enregistrer les entreprises mises à jour dans le fichier
                    file_put_contents($filename, json_encode($existingCompany, JSON_PRETTY_PRINT));

                    http_response_code(200); // Entreprise modifiée
                    echo json_encode(['message' => 'L\'entreprise a été modifiée avec succès.', 'url' => 'https://127.0.0.1:8000/api-ouverte/api-ouverte-ent.php?siren=' . $jsonData['Siren']], JSON_PRETTY_PRINT);
                } else {
                    http_response_code(404); // Aucune entreprise avec ce siren
                    echo json_encode(['message' => 'Aucune entreprise avec ce siren.'], JSON_PRETTY_PRINT);
                }
            } else {
                http_response_code(404); // Aucune entreprise trouvée
                echo json_encode(['message' => 'Aucune entreprise trouvée.'], JSON_PRETTY_PRINT);
            }
        } else {
            http_response_code(400); // Siren manquant
            echo json_encode(['message' => 'Le Siren est manquant dans la requête.'], JSON_PRETTY_PRINT);
        }
    } else {
        http_response_code(400); // JSON invalide
        echo json_encode(['message' => 'Le format du JSON est invalide.'], JSON_PRETTY_PRINT);
    }
}
