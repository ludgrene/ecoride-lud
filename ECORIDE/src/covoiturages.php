<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.html");
    exit;
}

// Affichage des messages d'erreur s'il y en a un
if (isset($_GET['error'])) {
    if ($_GET['error'] === "already_reserved") {
        echo '<div class="alert alert-danger text-center">Vous avez déjà réservé ce trajet.</div>';
    } elseif ($_GET['error'] === "full") {
        echo '<div class="alert alert-warning text-center">Ce trajet est complet.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Covoiturages - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- HEADER -->
    <?php include 'php/header.php'; ?>

    <!-- BANNIÈRE -->
    <section class="bg-success text-white text-center py-5">
        <h1>Découvrez nos covoiturages</h1>
        <?php if (isset($_GET['message']) && $_GET['message'] === 'reservation_success'): ?>
            <div id="alert-message" class="alert alert-success text-center">
                Réservation effectuée avec succès !
            </div>
        <?php endif; ?>

        <p>Choisissez un trajet qui vous correspond, économique et écologique.</p>
    </section>

    <!-- FORMULAIRE DE RECHERCHE -->
    <section class="search-section bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-4">Recherchez un itinéraire</h2>
            <form id="search-form" class="row g-3 justify-content-center">
                <div class="col-md-3">
                    <input type="text" name="depart" id="depart" class="form-control" placeholder="Ville de départ" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="arrivee" id="arrivee" class="form-control" placeholder="Ville d'arrivée" required>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" id="date" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Rechercher</button>
                </div>
            </form>
        </div>
    </section>

    <!-- FILTRES -->
    <section class="bg-light py-3">
        <div class="container">
            <h3 class="text-center mb-3">Affinez votre recherche</h3>
            <form id="filter-form" class="row g-3 justify-content-center">
                <div class="col-md-3">
                    <label for="ecologique" class="form-label">Voyage écologique :</label>
                    <select name="ecologique" id="ecologique" class="form-select">
                        <option value="">Tous</option>
                        <option value="1">Oui</option>
                        <option value="0">Non</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="prix_max" class="form-label">Prix maximum (€) :</label>
                    <input type="number" name="prix_max" id="prix_max" class="form-control" placeholder="Exemple : 50">
                </div>
                <div class="col-md-3">
                    <label for="duree_max" class="form-label">Durée maximum (minutes) :</label>
                    <input type="number" name="duree_max" id="duree_max" class="form-control" placeholder="Exemple : 120">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </section>

    <!-- LISTE DES COVOITURAGES -->
    <section class="container py-5">
        <h2 class="text-center mb-4">Résultats</h2>
        <div id="trajets-list" class="row g-4">
            <!-- Les résultats seront affichés ici par AJAX -->
        </div>
    </section>

    <div class="text-center mt-4">
        <button id="prevPage" class="btn btn-secondary">Précédent</button>
        <span id="pageNumber">1</span>
        <button id="nextPage" class="btn btn-secondary">Suivant</button>
    </div>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>Email : contact@ecoride.com</p>
            <a href="mentions-legales.html" class="text-white">Mentions légales</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>



    <script>
        document.addEventListener("DOMContentLoaded", function() {
            //  Suppression automatique des messages après 3 secondes
            const alertMessages = document.querySelectorAll(".alert");
            alertMessages.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            });

            //  Gestion de la pagination
            let currentPage = 1;
            const pageNumber = document.getElementById("pageNumber");
            const prevPage = document.getElementById("prevPage");
            const nextPage = document.getElementById("nextPage");

            if (prevPage && nextPage && pageNumber) {
                prevPage.addEventListener("click", () => {
                    if (currentPage > 1) {
                        currentPage--;
                        fetchResults(`page=${currentPage}`);
                        pageNumber.textContent = currentPage;
                    }
                });

                nextPage.addEventListener("click", () => {
                    currentPage++;
                    fetchResults(`page=${currentPage}`);
                    pageNumber.textContent = currentPage;
                });
            }

            //  Recherche dynamique et affichage des trajets
            const searchForm = document.getElementById("search-form");
            const trajetsList = document.getElementById("trajets-list");

            function fetchResults(params = "") {
                fetch(`php/covoiturages.php?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        trajetsList.innerHTML = "";

                        if (data.length > 0) {
                            data.forEach(trajet => {
                                const card = `
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">${trajet.ville_depart} ➔ ${trajet.ville_arrivee}</h5>
                                        <p class="card-text">Date : ${trajet.date_trajet}</p>
                                        <p class="card-text">Heure : ${trajet.heure_depart}</p>
                                        <p class="card-text">Prix : ${trajet.prix} €</p>
                                        <p class="card-text">Places disponibles : ${trajet.places_disponibles}</p>
                                        <form action="php/reservation.php" method="POST">
                                            <input type="hidden" name="trajet_id" value="${trajet.id}">
                                            <button type="submit" class="btn btn-primary" ${trajet.places_disponibles > 0 ? "" : "disabled"}>
                                                Réserver
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>`;
                                trajetsList.innerHTML += card;
                            });
                        } else {
                            trajetsList.innerHTML = `<p class="text-center text-danger">Aucun trajet trouvé.</p>`;
                        }
                    })
                    .catch(error => console.error("Erreur lors du chargement des trajets :", error));
            }

            // Charger les trajets dès l’ouverture de la page
            fetchResults();

            // Recherche dynamique
            if (searchForm) {
                searchForm.addEventListener("submit", function(event) {
                    event.preventDefault();
                    const formData = new FormData(searchForm);
                    const params = new URLSearchParams(formData).toString();
                    fetchResults(params);
                });
            }
        });
    </script>



</body>

</html>