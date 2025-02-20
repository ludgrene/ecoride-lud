<?php
session_start();
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: ../connexion.php");
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8",
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données.");
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$messages = [];

// Récupérer les informations actuelles de l'utilisateur
$stmt = $pdo->prepare("SELECT telephone, adresse, date_naissance FROM utilisateurs WHERE id = :id");
$stmt->execute([':id' => $utilisateur_id]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    die("Utilisateur introuvable.");
}

// Vérification et mise à jour des informations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telephone = trim(htmlspecialchars($_POST['telephone']));
    $adresse = trim(htmlspecialchars($_POST['adresse']));
    $date_naissance = $_POST['date_naissance'];

    if (empty($telephone) || empty($adresse) || empty($date_naissance)) {
        $messages[] = "Tous les champs doivent être remplis.";
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET telephone = :telephone, adresse = :adresse, date_naissance = :date_naissance WHERE id = :id");
        $stmt->execute([
            ':telephone' => $telephone,
            ':adresse' => $adresse,
            ':date_naissance' => $date_naissance,
            ':id' => $utilisateur_id
        ]);

        header("Location: profil.php?message=modification_success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mes informations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Modifier mes informations</h1>

        <?php if (!empty($messages)): ?>
            <div id="alert-message" class="alert alert-danger text-center">
                <ul class="mb-0">
                    <?php foreach ($messages as $message): ?>
                        <li><?= htmlspecialchars($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="mx-auto" style="max-width: 500px;">
            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="text" id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse</label>
                <input type="text" id="adresse" name="adresse" class="form-control" value="<?= htmlspecialchars($utilisateur['adresse'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="date_naissance" class="form-label">Date de naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" class="form-control" value="<?= htmlspecialchars($utilisateur['date_naissance'] ?? '') ?>" required>
            </div>

            <button type="submit" class="btn btn-success w-100">Enregistrer</button>
        </form>

        <div class="text-center mt-3">
            <a href="profil.php" class="btn btn-secondary">Retour au profil</a>
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