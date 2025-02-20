<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    die("Erreur de connexion : " . $e->getMessage());
}

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $prenom = trim(htmlspecialchars($_POST['prenom'] ?? ''));
    $pseudo = trim(htmlspecialchars($_POST['pseudo'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';

    // Vérification des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($pseudo) || empty($mot_de_passe) || empty($confirmer_mot_de_passe)) {
        $messages[] = "Tous les champs obligatoires doivent être remplis.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages[] = "Adresse email invalide.";
    }
    if ($mot_de_passe !== $confirmer_mot_de_passe) {
        $messages[] = "Les mots de passe ne correspondent pas.";
    }
    if (!preg_match('/^[a-zA-Z]{6,}$/', $mot_de_passe)) {
        $messages[] = "Le mot de passe doit contenir exactement 6 caractères alphabétiques (minuscules et majuscules uniquement).";
    }

    // Vérification unicité de l'email et du pseudo
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email OR pseudo = :pseudo");
    $stmt->execute([':email' => $email, ':pseudo' => $pseudo]);
    if ($stmt->rowCount() > 0) {
        $messages[] = "Cet email ou ce pseudo est déjà utilisé.";
    }

    // Si aucune erreur, insérer dans la base de données
    if (empty($messages)) {
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO utilisateurs (nom, prenom, email, pseudo, mot_de_passe, date_inscription, role_id) 
            VALUES (:nom, :prenom, :email, :pseudo, :mot_de_passe, NOW(), 1)");
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':pseudo' => $pseudo,
            ':mot_de_passe' => $mot_de_passe_hash
        ]);

        header("Location: ../connexion.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Inscription</h1>

        <!-- Messages d'alerte -->
        <?php if (!empty($messages)): ?>
            <div class="alert alert-danger text-center">
                <ul class="mb-0">
                    <?php foreach ($messages as $message): ?>
                        <li><?= $message ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center">
                Inscription réussie ! Vous pouvez maintenant vous <a href="../connexion.php">connecter</a>.
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <div class="container">
            <form method="POST" action="" class="mx-auto col-md-6">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" id="nom" name="nom" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="pseudo" class="form-label">Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3 position-relative">
                    <label for="mot_de_passe" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('mot_de_passe')">👁️</button>
                    </div>
                </div>

                <div class="mb-3 position-relative">
                    <label for="confirmer_mot_de_passe" class="form-label">Confirmer le mot de passe</label>
                    <div class="input-group">
                        <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirmer_mot_de_passe')">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100">S'inscrire</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const alertMessages = document.querySelectorAll(".alert");
            setTimeout(() => {
                alertMessages.forEach(alert => {
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";
                    setTimeout(() => alert.remove(), 500);
                });
            }, 3000);
        });

        function togglePassword(id) {
            let input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>
</body>

</html>