function genereDates() {
    const today = new Date();
    const jourSemaine  = today.getDay();
    const lundi  = new Date(today);
    while (lundi.getDay() !== 1) { 
        lundi.setDate(lundi.getDate() - 1);
    }

    const dates = [];
    for (let i = 0; i < 6; i++) { 
        const date = new Date(lundi );
        date.setDate(lundi.getDate() + i);
        dates.push(date);
    }
    return dates;
}


function afficherDatesSemaine() {
    const dates = genereDates();
    const Jours  = document.querySelectorAll('.grille .day a');

    dates.forEach((date, index) => {
        if (Jours[index]) {
            Jours[index].textContent = date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
            });
        }
    });
}



window.onload = afficherDatesSemaine;

document.addEventListener('DOMContentLoaded', function() {
    // Sélectionne tous les éléments <a> dans la section "Horraire"
    const horaires = document.querySelectorAll('.Horraire a');

    // Ajoute un événement de clic à chaque élément
    horaires.forEach(horaire => {
        horaire.addEventListener('click', function() {
            // Supprime la classe "selected" de tous les éléments
            horaires.forEach(h => h.classList.remove('selected'));
            
            // Ajoute la classe "selected" à l'élément cliqué
            this.classList.add('selected');
        });
    });
});
