<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRide</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- HEADER -->
    <?php include 'php/header.php'; ?>

    <!-- MESSAGE DE BIENVENUE -->
    <?php if (isset($_SESSION['utilisateur_nom'])): ?>
        <div class="alert alert-success text-center">
            Bienvenue, <?= htmlspecialchars($_SESSION['utilisateur_nom']); ?> !
            <a href="php/profil.php" class="btn btn-link text-decoration-none">Accédez à votre profil</a>
        </div>
    <?php endif; ?>

    <!-- BANNIÈRE -->
    <section class="position-relative text-white text-center" style="height: 60vh; overflow: hidden;">
        <img src="../public/accueil1.jpg" alt="Accueil" class="img-fluid w-100 h-100 object-fit-cover">
        <div class="position-absolute top-50 start-50 translate-middle text-shadow">
            <h1 class="display-4 fw-bold">Bienvenue sur EcoRide</h1>
            <p class="lead">Le covoiturage écologique pour un monde plus vert.</p>
        </div>
    </section>

    <!-- PRÉSENTATION -->
    <section class="about-section text-white py-5">
        <div class="container">
            <h2 class="text-center fade-in mb-5">Qui sommes-nous ?</h2>

            <!-- Texte et image alternés -->
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <p class="fade-in">
                        EcoRide est bien plus qu'une simple plateforme de covoiturage. Notre mission est de révolutionner vos déplacements en proposant une alternative écologique, économique et solidaire.
                    </p>
                </div>
                <div class="col-md-6">
                    <img src="../public/voiture-encharge.jpg" alt="Voiture en charge" class="img-fluid fade-in">
                </div>
            </div>

            <div class="row align-items-center mb-4">
                <div class="col-md-6 order-md-2">
                    <p class="fade-in">
                        Grâce à notre engagement envers un futur plus vert, EcoRide encourage les trajets effectués avec des voitures électriques ou hybrides.
                    </p>
                </div>
                <div class="col-md-6 order-md-1">
                    <img src="../public/route-vert.jpg" alt="Route verte" class="img-fluid fade-in">
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="fade-in">
                        Rejoignez notre communauté de voyageurs responsables et participez à la transformation des habitudes de transport pour un avenir meilleur.
                    </p>
                </div>
                <div class="col-md-6">
                    <img src="../public/co.jpg" alt="Image écologique" class="img-fluid fade-in">
                </div>
            </div>
        </div>
    </section>

    <!-- BARRE DE RECHERCHE -->
    <section class="search-section bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-4">Recherchez un itinéraire</h2>
            <form action="php/recherche.php" method="GET" class="row g-3 justify-content-center">
                <div class="col-md-3">
                    <label for="depart" class="form-label">Ville de départ</label>
                    <input type="text" name="depart" id="depart" class="form-control" placeholder="Ex : Paris" required>
                </div>
                <div class="col-md-3">
                    <label for="arrivee" class="form-label">Ville d'arrivée</label>
                    <input type="text" name="arrivee" id="arrivee" class="form-control" placeholder="Ex : Lyon" required>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Rechercher</button>
                </div>
            </form>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>Email : contact@ecoride.com</p>
            <a href="mentions-legales.html" class="text-white">Mentions légales</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>