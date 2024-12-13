<?php

include 'connexion.php';

function ajouterLivre($titre, $auteur, $nombre_pages, $genre, $date_publication, $statut_lecture, $couverture, $fichier_ebook, $format_ebook) {
    global $conn;
    
    // Validation plus stricte des entrées
    $titre = filter_var(trim($titre), FILTER_SANITIZE_STRING);
    $auteur = filter_var(trim($auteur), FILTER_SANITIZE_STRING);
    $genre = filter_var(trim($genre), FILTER_SANITIZE_STRING);
    
    // Validation plus stricte avec des longueurs maximales
    if (empty($titre) || strlen($titre) > 255 || empty($auteur) || strlen($auteur) > 255) {
        throw new Exception("Le titre et l'auteur sont obligatoires et ne doivent pas dépasser 255 caractères");
    }
    
    // Validation plus stricte du format de date
    $date_obj = DateTime::createFromFormat('Y-m-d', $date_publication);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $date_publication) {
        throw new Exception("Format de date invalide (format attendu : YYYY-MM-DD)");
    }
    
    // Validation des fichiers
    if (!empty($couverture) && !in_array(pathinfo($couverture, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
        throw new Exception("Format de couverture non autorisé");
    }
    
    if (!empty($fichier_ebook) && !in_array($format_ebook, ['epub', 'pdf', 'mobi'])) {
        throw new Exception("Format d'ebook non autorisé");
    }
    
    // Validation du nombre de pages
    if (!is_numeric($nombre_pages) || $nombre_pages < 0) {
        throw new Exception("Le nombre de pages doit être un nombre positif");
    }

    try {
        $sql = "INSERT INTO livres (titre, auteur, nombre_pages, genre, date_publication, statut_lecture, couverture, fichier_ebook, format_ebook) 
                VALUES (:titre, :auteur, :nombre_pages, :genre, :date_publication, :statut_lecture, :couverture, :fichier_ebook, :format_ebook)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':auteur', $auteur);
        $stmt->bindParam(':nombre_pages', $nombre_pages, PDO::PARAM_INT);
        $stmt->bindParam(':genre', $genre);
        $stmt->bindParam(':date_publication', $date_publication);
        $stmt->bindParam(':statut_lecture', $statut_lecture);
        $stmt->bindParam(':couverture', $couverture);
        $stmt->bindParam(':fichier_ebook', $fichier_ebook);
        $stmt->bindParam(':format_ebook', $format_ebook);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        // Log l'erreur réelle pour le débogage
        error_log("Erreur SQL : " . $e->getMessage());
        // Retourne un message générique à l'utilisateur
        throw new Exception("Une erreur est survenue lors de l'ajout du livre");
    }
}

function modifierLivre($id, $titre, $auteur, $nombre_pages, $genre, $date_publication, $statut_lecture, $couverture, $fichier_ebook, $format_ebook) {
    global $conn;

    // Nettoyage et validation des entrées
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $titre = filter_var(trim($titre), FILTER_SANITIZE_STRING);
    $auteur = filter_var(trim($auteur), FILTER_SANITIZE_STRING);
    $genre = filter_var(trim($genre), FILTER_SANITIZE_STRING);
    
    // Validation des données
    if (!$id) {
        throw new Exception("ID invalide");
    }

    if (empty($titre) || strlen($titre) > 255 || empty($auteur) || strlen($auteur) > 255) {
        throw new Exception("Le titre et l'auteur sont obligatoires et ne doivent pas dépasser 255 caractères");
    }

    // Validation de la date
    $date_obj = DateTime::createFromFormat('Y-m-d', $date_publication);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $date_publication) {
        throw new Exception("Format de date invalide (format attendu : YYYY-MM-DD)");
    }

    // Validation des fichiers
    if (!empty($couverture) && !in_array(pathinfo($couverture, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
        throw new Exception("Format de couverture non autorisé");
    }

    if (!empty($fichier_ebook) && !in_array($format_ebook, ['epub', 'pdf', 'mobi'])) {
        throw new Exception("Format d'ebook non autorisé");
    }

    // Validation du nombre de pages
    if (!is_numeric($nombre_pages) || $nombre_pages < 0) {
        throw new Exception("Le nombre de pages doit être un nombre positif");
    }

    try {
        $sql = "UPDATE livres SET titre = :titre, auteur = :auteur, nombre_pages = :nombre_pages, 
                genre = :genre, date_publication = :date_publication, statut_lecture = :statut_lecture, 
                couverture = :couverture, fichier_ebook = :fichier_ebook, format_ebook = :format_ebook 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':auteur', $auteur);
        $stmt->bindParam(':nombre_pages', $nombre_pages, PDO::PARAM_INT);
        $stmt->bindParam(':genre', $genre);
        $stmt->bindParam(':date_publication', $date_publication);
        $stmt->bindParam(':statut_lecture', $statut_lecture);
        $stmt->bindParam(':couverture', $couverture);
        $stmt->bindParam(':fichier_ebook', $fichier_ebook);
        $stmt->bindParam(':format_ebook', $format_ebook);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la modification du livre : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la modification du livre");
    }
}

function supprimerLivre($id) {
    if (!is_numeric($id) || $id <= 0) {
        throw new Exception("ID de livre invalide");
    }

    global $conn;
    
    try {
        // Vérifier si le livre existe avant de le supprimer
        $sql_check = "SELECT id FROM livres WHERE id = :id";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() === 0) {
            throw new Exception("Le livre n'existe pas");
        }

        $sql = "DELETE FROM livres WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if (!$stmt->execute()) {
            throw new Exception("Échec de la suppression du livre");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la suppression du livre : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la suppression du livre");
    }
}

function getLivres() {
    global $conn;
    try {
        $sql = "SELECT * FROM livres";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la récupération des livres : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la récupération des livres");
    }
}

function getLivreById($id) {
    if (!is_numeric($id) || $id <= 0) {
        throw new Exception("ID de livre invalide");
    }
    
    global $conn;
    try {
        $sql = "SELECT * FROM livres WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $livre = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$livre) {
            throw new Exception("Livre non trouvé");
        }
        
        return $livre;
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la récupération du livre : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la récupération du livre");
    }
}

function getLivresByAuteur($auteur) {
    if (empty($auteur) || strlen($auteur) > 255) {
        throw new Exception("Nom d'auteur invalide");
    }
    
    global $conn;
    try {
        $sql = "SELECT * FROM livres WHERE auteur = :auteur";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':auteur', $auteur);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la recherche par auteur : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la recherche des livres");
    }
}

function getLivresByGenre($genre) {
    if (empty($genre) || strlen($genre) > 255) {
        throw new Exception("Genre invalide");
    }
    
    global $conn;
    try {
        $sql = "SELECT * FROM livres WHERE genre = :genre";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':genre', $genre);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la recherche par genre : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la recherche des livres");
    }
}

function getLivresByDate($date_publication) {
    $date_obj = DateTime::createFromFormat('Y-m-d', $date_publication);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $date_publication) {
        throw new Exception("Format de date invalide (format attendu : YYYY-MM-DD)");
    }
    
    global $conn;
    try {
        $sql = "SELECT * FROM livres WHERE date_publication = :date_publication";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':date_publication', $date_publication);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la recherche par date : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la recherche des livres");
    }
}

function getLivresByStatut($statut_lecture) {
    $statuts_valides = ['lu', 'en cours', 'à lire'];
    if (!in_array($statut_lecture, $statuts_valides)) {
        throw new Exception("Statut de lecture invalide");
    }
    
    global $conn;
    try {
        $sql = "SELECT * FROM livres WHERE statut_lecture = :statut_lecture";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':statut_lecture', $statut_lecture);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la recherche par statut : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la recherche des livres");
    }
}

function getLivresByTitre($titre) {
    if (empty($titre) || strlen($titre) > 255) {
        throw new Exception("Titre invalide");
    }
    
    global $conn;
    try {
        $titre = "%" . $titre . "%";
        $sql = "SELECT * FROM livres WHERE titre LIKE :titre";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':titre', $titre);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur SQL lors de la recherche par titre : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la recherche des livres");
    }
}
