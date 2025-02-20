document.addEventListener("DOMContentLoaded", function () {
    // Animation au défilement
    const elements = document.querySelectorAll(".fade-in");
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("show");
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1 }
    );
    elements.forEach((el) => observer.observe(el));

    // Validation et recherche dynamique
    const searchForm = document.getElementById("search-form");
    const filterForm = document.getElementById("filter-form");
    const trajetsList = document.getElementById("trajets-list");

    // Fonction pour récupérer et afficher les résultats
    function fetchResults(params) {
        fetch(`php/covoiturages.php?${params}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                // Réinitialise les résultats
                trajetsList.innerHTML = "";

                // Affiche les trajets ou un message s'il n'y en a pas
                if (data.length > 0) {
                    data.forEach((trajet) => {
                        const card = `
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">${trajet.ville_depart} ➔ ${trajet.ville_arrivee}</h5>
                                        <p class="card-text">Date : ${trajet.date_trajet}</p>
                                        <p class="card-text">Heure : ${trajet.heure_depart}</p>
                                        <p class="card-text">Prix : ${trajet.prix} €</p>
                                        <p class="card-text">Durée : ${trajet.duree} minutes</p>
                                        <p class="card-text">Places disponibles : ${trajet.places_disponibles}</p>
                                        <p class="card-text">Écologique : ${trajet.ecologique === "1" ? "Oui" : "Non"}</p>
                                    </div>
                                </div>
                            </div>`;
                        trajetsList.innerHTML += card;
                    });
                } else {
                    showMessage(trajetsList, "Aucun trajet trouvé.", "text-danger");
                }
            })
            .catch((error) => {
                console.error("Erreur lors de la récupération des trajets :", error);
                showMessage(trajetsList, "Une erreur est survenue. Veuillez réessayer.", "text-danger");
            });
    }

    // Gestion du formulaire de recherche
    if (searchForm) {
        searchForm.addEventListener("submit", function (event) {
            event.preventDefault(); 
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData).toString();
            fetchResults(params);
        });
    }

    // Gestion du formulaire de filtres
    if (filterForm) {
        filterForm.addEventListener("submit", function (event) {
            event.preventDefault(); 

            const formData = new FormData(searchForm); 
            const filterData = new FormData(filterForm); 
            for (const [key, value] of filterData.entries()) {
                formData.append(key, value);
            }

            // Convertit les données en paramètres URL
            const params = new URLSearchParams(formData).toString();
            fetchResults(params);
        });
    }

    // Fonction pour afficher les messages
    function showMessage(container, message, className) {
        container.innerHTML = `<p class="text-center ${className}">${message}</p>`;
    }
});

// Cacher le message de bienvenue après 3 secondes
document.addEventListener("DOMContentLoaded", function () {
    const alertMessage = document.querySelector(".alert-success");
    if (alertMessage) {
        setTimeout(() => {
            alertMessage.style.transition = "opacity 0.5s ease";
            alertMessage.style.opacity = "0"; // Rendre l'élément invisible
            setTimeout(() => alertMessage.remove(), 500); // Supprimer l'élément après l'animation
        }, 3000); // Délai de 3 secondes avant de commencer l'animation
    }
});
