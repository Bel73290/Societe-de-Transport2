// Récupération des éléments principaux
const menuItems = document.querySelectorAll('.menu-item');
const hiddenContents = document.querySelectorAll('.hidden-content');
const searchBox = document.getElementById('search-box');
const employeeLogin = document.getElementById('employee-login');
const clientLogin = document.getElementById('client-login');
const resultContainer = document.querySelector('#search-box .search-result'); // Correctement ciblé dans "search-box"

// Au chargement de la page, cacher tous les contenus dynamiques
window.onload = () => {
    hideAll(); // Assure que tous les contenus sont masqués au départ
};

// Ajout des événements de clic sur les éléments de menu
menuItems.forEach(item => {
    item.addEventListener('click', (event) => {
        event.stopPropagation(); // Empêche le clic global de fermer le contenu
        const action = item.dataset.action;

        // Cacher tous les contenus avant d'afficher le bon
        hideAll();

        // Afficher le contenu correspondant
        if (action === "search") {
            searchBox.style.display = "flex"; // Afficher la barre de recherche
        } else if (action === "employees") {
            employeeLogin.style.display = "block"; // Afficher le login employé
        } else if (action === "clients") {
            clientLogin.style.display = "block"; // Afficher le login client
        }
    });
});

// Empêcher la fermeture lorsque l'utilisateur clique sur les contenus dynamiques
hiddenContents.forEach(content => {
    content.addEventListener('click', (event) => {
        event.stopPropagation(); // Empêche le clic global de cacher le contenu
    });
});

// Ajout d'un événement global pour fermer les contenus lorsqu'on clique ailleurs
document.addEventListener('click', () => {
    hideAll(); // Masquer tous les contenus
});

// Fonction pour cacher tous les contenus
function hideAll() {
    if (searchBox) searchBox.style.display = "none"; // Cacher la barre de recherche
    if (employeeLogin) employeeLogin.style.display = "none"; // Cacher le login employé
    if (clientLogin) clientLogin.style.display = "none"; // Cacher le login client
    if (resultContainer) resultContainer.innerHTML = ""; // Réinitialiser les résultats de recherche
}

// Cibler le formulaire de recherche
const searchForm = document.querySelector('form');

// Ajouter un écouteur d'événement pour la soumission du formulaire
if (searchForm) {
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Empêche le rechargement de la page
        const colis = document.getElementById('colis').value; // Récupère la valeur du numéro de colis

        // Si tu souhaites envoyer la recherche par AJAX, tu peux le faire ici
        console.log("Recherche pour le colis: ", colis); // Par exemple, loguer la recherche

        // Ensuite, tu peux faire une requête AJAX ou afficher les résultats sans recharger la page
        // Exemple d'appel AJAX (tu peux adapter selon ton besoin)
        fetch('/ta-page-de-recherche', {
            method: 'POST',
            body: new URLSearchParams({ 'colis': colis, 'submit_search': 'true' })
        })
        .then(response => response.text())
        .then(data => {
            resultContainer.innerHTML = data; // Afficher les résultats sans recharger la page
        })
        .catch(error => console.error('Erreur:', error));
    });
}
