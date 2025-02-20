<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['utilisateur_id'])) {
    echo json_encode(["error" => "Accès refusé. Veuillez vous connecter."]);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php'; // Charge Composer et Dotenv

use Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Connexion à la base de données
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur de connexion à la base de données"]);
    exit;
}

// Récupération et sécurisation des paramètres
$depart = htmlspecialchars($_GET['depart'] ?? '');
$arrivee = htmlspecialchars($_GET['arrivee'] ?? '');
$date = htmlspecialchars($_GET['date'] ?? '');
$ecologique = htmlspecialchars($_GET['ecologique'] ?? '');
$prix_max = htmlspecialchars($_GET['prix_max'] ?? '');
$duree_max = htmlspecialchars($_GET['duree_max'] ?? '');

// Si aucun critère de recherche, afficher des trajets par défaut (aléatoires)
if (empty($depart) && empty($arrivee) && empty($date)) {
    $sql = "SELECT * FROM trajets ORDER BY RAND() LIMIT 5"; // Sélectionne 5 trajets aléatoires
    $stmt = $pdo->query($sql);
} else {
    // Construction de la requête avec filtres
    $sql = "SELECT * FROM trajets WHERE 1=1";
    $params = [];

    if (!empty($depart)) {
        $sql .= " AND ville_depart = :depart";
        $params[':depart'] = $depart;
    }
    if (!empty($arrivee)) {
        $sql .= " AND ville_arrivee = :arrivee";
        $params[':arrivee'] = $arrivee;
    }
    if (!empty($date)) {
        $sql .= " AND date_trajet = :date";
        $params[':date'] = $date;
    }
    if ($ecologique !== '') {
        $sql .= " AND ecologique = :ecologique";
        $params[':ecologique'] = $ecologique;
    }
    if (!empty($prix_max)) {
        $sql .= " AND prix <= :prix_max";
        $params[':prix_max'] = $prix_max;
    }
    if (!empty($duree_max)) {
        $sql .= " AND duree <= :duree_max";
        $params[':duree_max'] = $duree_max;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($trajets)) {
    echo json_encode(["message" => "Aucun trajet trouvé."]);
} else {
    echo json_encode($trajets);
}

// Nombre de trajets par page
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ajouter la pagination à la requête SQL
$sql .= " LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset;
