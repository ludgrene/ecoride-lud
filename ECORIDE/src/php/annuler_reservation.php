<?php
session_start();

if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: ../connexion.html");
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8", $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données");
}

// Vérifier que la requête est bien envoyée
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reservation_id"])) {
    $reservation_id = intval($_POST["reservation_id"]);
    $utilisateur_id = $_SESSION['utilisateur_id'];

    // Vérifier que la réservation existe bien et appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :reservation_id AND utilisateur_id = :utilisateur_id");
    $stmt->execute([':reservation_id' => $reservation_id, ':utilisateur_id' => $utilisateur_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reservation) {
        // Récupérer l'ID du trajet avant de supprimer la réservation
        $trajet_id = $reservation['trajet_id'];

        // Supprimer la réservation
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = :reservation_id");
        $stmt->execute([':reservation_id' => $reservation_id]);

        // Rendre la place disponible dans le trajet
        $stmt = $pdo->prepare("UPDATE trajets SET places_disponibles = places_disponibles + 1 WHERE id = :trajet_id");
        $stmt->execute([':trajet_id' => $trajet_id]);

        // Rediriger avec message de succès
        header("Location: profil.php?message=reservation_annulee");
        exit;
    } else {
        // Si la réservation n'existe pas
        header("Location: profil.php?message=erreur_annulation");
        exit;
    }
} else {
    // Si la requête est incorrecte
    header("Location: profil.php?message=requete_invalide");
    exit;
}
