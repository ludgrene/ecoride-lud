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

$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données");
}

// Récupération des données
$trajet_id = $_POST['trajet_id'] ?? null;
$utilisateur_id = $_SESSION['utilisateur_id'];

if (!$trajet_id) {
    die("Erreur : Trajet non spécifié.");
}

// Vérifie si des places sont encore disponibles
$stmt = $pdo->prepare("SELECT places_disponibles FROM trajets WHERE id = :trajet_id");
$stmt->execute([':trajet_id' => $trajet_id]);
$trajet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trajet || $trajet['places_disponibles'] <= 0) {
    header("Location: ../covoiturages.php?error=full");
    exit;
}

// Vérifie si l'utilisateur est déjà inscrit à ce trajet
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE utilisateur_id = :utilisateur_id AND trajet_id = :trajet_id");
$stmt->execute([
    ':utilisateur_id' => $utilisateur_id,
    ':trajet_id' => $trajet_id
]);

if ($stmt->rowCount() > 0) {
    header("Location: ../covoiturages.php?error=already_reserved");
    exit;
}

// Insère la réservation
$stmt = $pdo->prepare("INSERT INTO reservations (utilisateur_id, trajet_id) VALUES (:utilisateur_id, :trajet_id)");
$stmt->execute([
    ':utilisateur_id' => $utilisateur_id,
    ':trajet_id' => $trajet_id
]);

// Met à jour le nombre de places disponibles
$stmt = $pdo->prepare("UPDATE trajets SET places_disponibles = places_disponibles - 1 WHERE id = :trajet_id");
$stmt->execute([':trajet_id' => $trajet_id]);

// Redirection après réservation
header("Location: ../covoiturages.php?message=reservation_success");
exit;
