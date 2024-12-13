<?php

include 'fonctions.php';

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Constantes pour les messages
const ERROR_INVALID_ACTION = 'Action non valide';
const ERROR_MISSING_DATA = 'Données manquantes';
const ERROR_INVALID_JSON = 'Format JSON invalide';
const SUCCESS_DELETE = 'Livre supprimé avec succès';
const SUCCESS_ADD = 'Livre ajouté avec succès';
const SUCCESS_UPDATE = 'Livre modifié avec succès';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée', 405);
    }

    if (!isset($_GET['action'])) {
        throw new Exception('Action non spécifiée', 400);
    }

    $action = $_GET['action'];
    
    switch($action) {
        case 'getLivresByAuteur':
            if (!isset($_POST['auteur']) || empty($_POST['auteur'])) {
                throw new Exception('Paramètre auteur manquant', 400);
            }
            $auteur = htmlspecialchars($_POST['auteur']);
            $livres = getLivresByAuteur($auteur);
            echo json_encode($livres);
            break;

        case 'getLivresByTitre':
            if (!isset($_POST['titre']) || empty($_POST['titre'])) {
                throw new Exception('Paramètre titre manquant', 400);
            }
            $titre = htmlspecialchars($_POST['titre']);
            $livres = getLivresByTitre($titre);
            echo json_encode($livres);
            break;

        case 'getLivresByGenre':
            if (!isset($_POST['genre']) || empty($_POST['genre'])) {
                throw new Exception('Paramètre genre manquant', 400);
            }
            $genre = htmlspecialchars($_POST['genre']);
            $livres = getLivresByGenre($genre);
            echo json_encode($livres);
            break;

        case 'getLivresByDate':
            if (!isset($_POST['date']) || empty($_POST['date'])) {
                throw new Exception('Paramètre date manquant', 400);
            }
            if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $_POST['date'])) {
                throw new Exception('Format de date invalide (YYYY-MM-DD attendu)', 400);
            }
            $date = $_POST['date'];
            $livres = getLivresByDate($date);
            echo json_encode($livres);
            break;

        case 'supprimerLivre':
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception('ID de livre invalide', 400);
            }
            $id = (int)$_POST['id'];
            supprimerLivre($id);
            http_response_code(200);
            echo json_encode(['message' => SUCCESS_DELETE]);
            break;

        case 'ajouterLivre':
            if (!isset($_POST['livre'])) {
                throw new Exception(ERROR_MISSING_DATA, 400);
            }
            
            $livre = $_POST['livre'];
            $data = json_decode($livre, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(ERROR_INVALID_JSON, 400);
            }
            
            // Validation des champs requis
            $required_fields = ['titre', 'auteur', 'genre'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Le champ $field est requis", 400);
                }
            }
            
            // Nettoyage et validation des données
            $data = array_map('htmlspecialchars', $data);
            
            ajouterLivre(
                $data['titre'],
                $data['auteur'],
                isset($data['nombre_pages']) ? (int)$data['nombre_pages'] : null,
                $data['genre'],
                isset($data['date_publication']) ? $data['date_publication'] : null,
                isset($data['statut_lecture']) ? $data['statut_lecture'] : null,
                isset($data['couverture']) ? $data['couverture'] : null,
                isset($data['fichier_ebook']) ? $data['fichier_ebook'] : null,
                isset($data['format_ebook']) ? $data['format_ebook'] : null
            );
            
            http_response_code(201);
            echo json_encode(['message' => SUCCESS_ADD]);
            break;

        case 'getLivres':
            $livres = getLivres();
            http_response_code(200);
            echo json_encode($livres);
            break;

        case 'modifierLivre':
            if (!isset($_POST['livre'])) {
                throw new Exception(ERROR_MISSING_DATA, 400);
            }
            
            $livre = $_POST['livre'];
            $data = json_decode($livre, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(ERROR_INVALID_JSON, 400);
            }
            
            if (!isset($data['id']) || !is_numeric($data['id'])) {
                throw new Exception('ID de livre invalide', 400);
            }
            
            // Nettoyage et validation des données
            $data = array_map('htmlspecialchars', $data);
            
            modifierLivre(
                (int)$data['id'],
                $data['titre'],
                $data['auteur'],
                isset($data['nombre_pages']) ? (int)$data['nombre_pages'] : null,
                $data['genre'],
                isset($data['date_publication']) ? $data['date_publication'] : null,
                isset($data['statut_lecture']) ? $data['statut_lecture'] : null,
                isset($data['couverture']) ? $data['couverture'] : null,
                isset($data['fichier_ebook']) ? $data['fichier_ebook'] : null,
                isset($data['format_ebook']) ? $data['format_ebook'] : null
            );
            
            http_response_code(200);
            echo json_encode(['message' => SUCCESS_UPDATE]);
            break;

        default:
            throw new Exception(ERROR_INVALID_ACTION, 400);
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $code
    ]);
}
