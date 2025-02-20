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

// Récupérer les informations de l'utilisateur connecté
$utilisateur_id = $_SESSION['utilisateur_id'];
$stmt = $pdo->prepare("SELECT nom, prenom, pseudo, email, date_inscription FROM utilisateurs WHERE id = :id");
$stmt->execute([':id' => $utilisateur_id]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    die("Utilisateur introuvable.");
}

// Récupérer les réservations de l'utilisateur
$stmt = $pdo->prepare("SELECT trajets.*, reservations.id AS reservation_id 
                       FROM reservations 
                       JOIN trajets ON reservations.trajet_id = trajets.id
                       WHERE reservations.utilisateur_id = :utilisateur_id");
$stmt->execute([':utilisateur_id' => $utilisateur_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Mon Profil</h1>

        <!-- Affichage des messages -->
        <?php if (isset($_GET['message'])): ?>
            <div id="alert-message" class="alert text-center 
                <?= $_GET['message'] === 'reservation_annulee' ? 'alert-success' : ($_GET['message'] === 'erreur_annulation' ? 'alert-danger' : 'alert-warning'); ?>">
                <?= ($_GET['message'] === 'reservation_annulee') ? 'Votre réservation a été annulée avec succès.' : (($_GET['message'] === 'erreur_annulation') ? 'Erreur : Impossible d\'annuler cette réservation.' :
                        'Requête invalide.'); ?>
            </div>
        <?php endif; ?>

        <!-- Affichage des informations utilisateur -->
        <p><strong>Nom :</strong> <?= htmlspecialchars($utilisateur['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($utilisateur['prenom']) ?></p>
        <p><strong>Pseudo :</strong> <?= htmlspecialchars($utilisateur['pseudo']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($utilisateur['email']) ?></p>
        <p><strong>Date d'inscription :</strong> <?= $utilisateur['date_inscription'] ?></p>

        <!-- Bouton pour modifier les informations -->
        <a href="modif-info.php" class="btn btn-warning">Modifier mes informations</a>

        <hr>

        <!-- Liste des réservations -->
        <h2 class="mt-5">Mes Réservations</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Départ</th>
                    <th>Arrivée</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Prix</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr>
                        <td colspan='6' class='text-center text-muted'>Aucune réservation</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservations as $trajet): ?>
                        <tr>
                            <td><?= htmlspecialchars($trajet['ville_depart']) ?></td>
                            <td><?= htmlspecialchars($trajet['ville_arrivee']) ?></td>
                            <td><?= htmlspecialchars($trajet['date_trajet']) ?></td>
                            <td><?= htmlspecialchars($trajet['heure_depart']) ?></td>
                            <td><?= htmlspecialchars($trajet['prix']) ?> €</td>
                            <td>
                                <form action="annuler_reservation.php" method="POST">
                                    <input type="hidden" name="reservation_id" value="<?= $trajet['reservation_id'] ?>">
                                    <button type="submit" class="btn btn-danger">Annuler</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Lien vers l'historique des covoiturages -->
        <div class="text-center mt-4">
            <a href="historique.php" class="btn btn-secondary">Voir mon historique de covoiturages</a>
        </div>

        <!-- Bouton retour -->
        <div class="text-center mt-4">
            <a href="../index.php" class="btn btn-primary">Retour à l'accueil</a>
        </div>
    </div>

    <!-- Script pour faire disparaître le message après 3 secondes -->
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