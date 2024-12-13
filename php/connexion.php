<?php

$host = "localhost";
$port = "5432";
$dbname = "BlueReading";
$user = "postgres";
$password = "Hachi3334*";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    // Configurer PDO pour qu'il lance des exceptions en cas d'erreur
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données";
} catch(PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}
?>
