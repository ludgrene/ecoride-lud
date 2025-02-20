<?php
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="bg-light py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a href="index.php">
            <img src="/public/logo.png" alt="EcoRide Logo" width="150">
        </a>

        <!-- Navigation -->
        <nav>
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="index.php">Accueil</a>
                </li>

                <?php if (isset($_SESSION['utilisateur_id'])): ?>
                    <!-- Si l'utilisateur est connecté, afficher "Covoiturages", "Mon Profil" et "Déconnexion" -->
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="covoiturages.php">Covoiturages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="php/profil.php">Mon Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="php/deconnexion.php">Déconnexion</a>
                    </li>
                <?php else: ?>
                    <!-- Si l'utilisateur N'EST PAS connecté, afficher "Connexion" et "Inscription" -->
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/src/connexion.php">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="php/inscription.php">Inscription</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>