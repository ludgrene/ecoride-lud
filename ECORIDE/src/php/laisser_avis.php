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
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8",
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données");
}

$messages = [];

// Récupérer les trajets disponibles pour cet utilisateur
$stmt = $pdo->prepare("
    SELECT t.id, t.ville_depart, t.ville_arrivee, t.date_trajet, t.heure_depart, t.prix, u.nom AS chauffeur_nom, t.conducteur_id
    FROM reservations r
    JOIN trajets t ON r.trajet_id = t.id
    JOIN utilisateurs u ON t.conducteur_id = u.id
    WHERE r.utilisateur_id = :utilisateur_id
");
$stmt->execute([':utilisateur_id' => $_SESSION['utilisateur_id']]);
$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["trajet_id"], $_POST["note"], $_POST["commentaire"])) {
    $trajet_id = intval($_POST["trajet_id"]);
    $utilisateur_id = $_SESSION['utilisateur_id'];
    $note = intval($_POST["note"]);
    $commentaire = trim(htmlspecialchars($_POST["commentaire"]));

    // Vérifier si l'utilisateur a bien réservé ce trajet
    $stmt = $pdo->prepare("
        SELECT t.conducteur_id 
        FROM reservations r
        JOIN trajets t ON r.trajet_id = t.id
        WHERE r.utilisateur_id = :utilisateur_id AND r.trajet_id = :trajet_id
    ");
    $stmt->execute([
        ':utilisateur_id' => $utilisateur_id,
        ':trajet_id' => $trajet_id
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $messages[] = "Erreur : Vous ne pouvez laisser un avis que pour un trajet réservé.";
    } else {
        $conducteur_id = $result['conducteur_id'];

        // Vérifier si l'utilisateur n'a pas déjà laissé un avis pour ce trajet
        $stmt = $pdo->prepare("
            SELECT id FROM avis 
            WHERE utilisateur_id = :utilisateur_id AND trajet_id = :trajet_id
        ");
        $stmt->execute([
            ':utilisateur_id' => $utilisateur_id,
            ':trajet_id' => $trajet_id
        ]);

        if ($stmt->rowCount() > 0) {
            $messages[] = "Erreur : Vous avez déjà noté ce trajet.";
        } else {
            // Insérer l'avis dans la base de données
            $stmt = $pdo->prepare("
                INSERT INTO avis (utilisateur_id, trajet_id, conducteur_id, note, commentaire, date_avis)
                VALUES (:utilisateur_id, :trajet_id, :conducteur_id, :note, :commentaire, NOW())
            ");
            $stmt->execute([
                ':utilisateur_id' => $utilisateur_id,
                ':trajet_id' => $trajet_id,
                ':conducteur_id' => $conducteur_id,
                ':note' => $note,
                ':commentaire' => $commentaire
            ]);

            header("Location: historique.php?message=review_success");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laisser un Avis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Laisser un Avis</h1>

        <?php if (!empty($messages)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($messages as $message): ?>
                        <li><?= htmlspecialchars($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="trajet_id" class="form-label">Trajet :</label>
                <select name="trajet_id" id="trajet_id" class="form-select" required>
                    <option value="">Sélectionnez un trajet</option>
                    <?php foreach ($trajets as $trajet): ?>
                        <option value="<?= $trajet['id'] ?>" data-chauffeur="<?= $trajet['conducteur_id'] ?>">
                            <?= "{$trajet['ville_depart']} ➔ {$trajet['ville_arrivee']} (Chauffeur: {$trajet['chauffeur_nom']})" ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="chauffeur_id" id="chauffeur_id">
            </div>

            <div class="mb-3">
                <label for="note" class="form-label">Note :</label>
                <select name="note" id="note" class="form-select" required>
                    <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                    <option value="4">⭐⭐⭐⭐ (4)</option>
                    <option value="3">⭐⭐⭐ (3)</option>
                    <option value="2">⭐⭐ (2)</option>
                    <option value="1">⭐ (1)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="commentaire" class="form-label">Commentaire :</label>
                <textarea name="commentaire" id="commentaire" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Envoyer mon avis</button>
        </form>

        <div class="text-center mt-4">
            <a href="historique.php" class="btn btn-secondary">Retour à l'historique</a>
        </div>
    </div>

    <script>
        document.getElementById("trajet_id").addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById("chauffeur_id").value = selectedOption.getAttribute("data-chauffeur");
        });
    </script>
</body>

</html>