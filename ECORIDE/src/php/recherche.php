<?php
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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur de connexion à la base de données"]);
    exit;
}

// Initialisation des variables
$trajets = [];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 5; // Nombre de résultats par page
$offset = ($page - 1) * $results_per_page;

// Vérification des données du formulaire
if (isset($_GET['depart'], $_GET['arrivee'], $_GET['date'])) {
    // Récupération et sécurisation des données
    $depart = htmlspecialchars(trim($_GET['depart']));
    $arrivee = htmlspecialchars(trim($_GET['arrivee']));
    $date = htmlspecialchars(trim($_GET['date']));

    // Validation des champs
    if (!empty($depart) && !empty($arrivee) && !empty($date) && DateTime::createFromFormat('Y-m-d', $date)) {
        // Requête SQL pour paginer les résultats
        $sql = "SELECT * FROM trajets 
                WHERE ville_depart = :depart 
                  AND ville_arrivee = :arrivee 
                  AND date_trajet = :date 
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':depart', $depart, PDO::PARAM_STR);
        $stmt->bindValue(':arrivee', $arrivee, PDO::PARAM_STR);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compter le nombre total de résultats
        $total_results_stmt = $pdo->prepare("SELECT COUNT(*) FROM trajets 
                                             WHERE ville_depart = :depart 
                                               AND ville_arrivee = :arrivee 
                                               AND date_trajet = :date");
        $total_results_stmt->execute([':depart' => $depart, ':arrivee' => $arrivee, ':date' => $date]);
        $total_results = $total_results_stmt->fetchColumn();
        $total_pages = ceil($total_results / $results_per_page);
    } else {
        $error_message = "Les champs sont vides ou la date est invalide. Veuillez vérifier votre saisie.";
    }
} else {
    $error_message = "Formulaire non soumis ou incomplet.";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de la recherche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Résultats de votre recherche</h1>

        <?php if (!empty($error_message)): ?>
            <p class="text-center text-danger"><?= htmlspecialchars($error_message) ?></p>
            <a href="../index.html" class="btn btn-primary mt-4 d-block mx-auto" style="width: fit-content;">Nouvelle recherche</a>
        <?php elseif (empty($trajets)): ?>
            <p class="text-center text-danger">Aucun trajet trouvé pour les critères donnés.</p>
            <a href="../index.php" class="btn btn-primary mt-4 d-block mx-auto" style="width: fit-content;">Nouvelle recherche</a>
        <?php else: ?>
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Départ</th>
                        <th>Arrivée</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Prix</th>
                        <th>Places disponibles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trajets as $trajet): ?>
                        <tr>
                            <td><?= htmlspecialchars($trajet['ville_depart']) ?></td>
                            <td><?= htmlspecialchars($trajet['ville_arrivee']) ?></td>
                            <td><?= htmlspecialchars($trajet['date_trajet']) ?></td>
                            <td><?= htmlspecialchars($trajet['heure_depart']) ?></td>
                            <td><?= htmlspecialchars($trajet['prix']) ?> €</td>
                            <td><?= htmlspecialchars($trajet['places_disponibles']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?depart=<?= urlencode($depart) ?>&arrivee=<?= urlencode($arrivee) ?>&date=<?= urlencode($date) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <a href="../index.php" class="btn btn-primary mt-4 d-block mx-auto" style="width: fit-content;">Nouvelle recherche</a>
        <?php endif; ?>
    </div>
</body>

</html>