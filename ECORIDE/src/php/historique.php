<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.html");
    exit;
}

// Connexion à la base de données
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

// Récupération des trajets réservés
$utilisateur_id = $_SESSION['utilisateur_id'];
$stmt = $pdo->prepare("SELECT trajets.*, reservations.id AS reservation_id 
                        FROM reservations 
                        JOIN trajets ON reservations.trajet_id = trajets.id 
                        WHERE reservations.utilisateur_id = :utilisateur_id
                        ORDER BY trajets.date_trajet DESC");
$stmt->execute([':utilisateur_id' => $utilisateur_id]);
$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion des messages
$message = "";
if (isset($_GET['message'])) {
    if ($_GET['message'] === "annulation_reussie") {
        $message = "Votre réservation a été annulée avec succès.";
    } elseif ($_GET['message'] === "erreur_annulation") {
        $message = "Erreur lors de l’annulation de votre réservation.";
    }
}
?>
<?php if (isset($_GET['message'])): ?>
    <div class="alert text-center 
        <?= ($_GET['message'] == 'review_success') ? 'alert-success' : 'alert-danger' ?>">
        <?php
        switch ($_GET['message']) {
            case 'review_success':
                echo "Votre avis a été enregistré avec succès.";
                break;
            case 'error_no_reservation':
                echo "Erreur : Vous ne pouvez laisser un avis que pour un trajet réservé.";
                break;
            case 'error_already_reviewed':
                echo "Erreur : Vous avez déjà noté ce trajet.";
                break;
            case 'invalid_request':
                echo "Erreur : Requête invalide.";
                break;
        }
        ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Covoiturages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>


    <div class="container py-5">
        <h1 class="text-center mb-4">Mon Historique de Covoiturages</h1>

        <?php if (!empty($message)): ?>
            <div id="alert-message" class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <h3>🚗 Trajets à venir</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Départ</th>
                            <th>Arrivée</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $trajets_avenir = array_filter($trajets, fn($t) => strtotime($t['date_trajet']) >= time());
                        if (empty($trajets_avenir)) {
                            echo "<tr><td colspan='5' class='text-center text-muted'>Aucun trajet à venir</td></tr>";
                        } else {
                            foreach ($trajets_avenir as $trajet) {
                                echo "<tr>
                                    <td>{$trajet['ville_depart']}</td>
                                    <td>{$trajet['ville_arrivee']}</td>
                                    <td>{$trajet['date_trajet']}</td>
                                    <td>{$trajet['heure_depart']}</td>
                                    <td>
                                        <form action='php/annuler_reservation.php' method='POST'>
                                            <input type='hidden' name='reservation_id' value='{$trajet['reservation_id']}'>
                                            <button type='submit' class='btn btn-danger btn-sm'>Annuler</button>
                                        </form>
                                    </td>
                                  </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h3>📜 Trajets passés</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Départ</th>
                            <th>Arrivée</th>
                            <th>Date</th>
                            <th>Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $trajets_passes = array_filter($trajets, fn($t) => strtotime($t['date_trajet']) < time());
                        if (empty($trajets_passes)) {
                            echo "<tr><td colspan='4' class='text-center text-muted'>Aucun trajet passé</td></tr>";
                        } else {
                            foreach ($trajets_passes as $trajet) {
                                echo "<tr>
                                    <td>{$trajet['ville_depart']}</td>
                                    <td>{$trajet['ville_arrivee']}</td>
                                    <td>{$trajet['date_trajet']}</td>
                                    <td>{$trajet['heure_depart']}</td>
                                  </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <a href="laisser_avis.php" class="btn btn-warning">Laisser un avis</a>
        </div>


        <!-- Bouton retour -->
        <div class="text-center mt-4">
            <a href="profil.php" class="btn btn-primary">Retour à Mon Profil</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const alertMessage = document.getElementById("alert-message");
            if (alertMessage) {
                setTimeout(() => {
                    alertMessage.style.transition = "opacity 0.5s ease";
                    alertMessage.style.opacity = "0";
                    setTimeout(() => alertMessage.remove(), 500);
                }, 3000);
            }
        });
    </script>

</body>

</html>