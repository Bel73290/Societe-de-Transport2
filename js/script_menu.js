function genereDates() {
    const today = new Date();
    const jourSemaine = today.getDay();
    const lundi = new Date(today);
    while (lundi.getDay() !== 1) { 
        lundi.setDate(lundi.getDate() - 1);
    }

    const dates = [];
    for (let i = 0; i < 6; i++) { 
        const date = new Date(lundi);
        date.setDate(lundi.getDate() + i);
        dates.push(date);
    }
    return dates;
}

function afficherDatesSemaine() {
    const dates = genereDates();
    const Jours = document.querySelectorAll('.grille .day a');

    dates.forEach((date, index) => {
        if (Jours[index]) {
            const jourTexte = date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
            });

            // Met à jour le texte de l'élément
            Jours[index].textContent = jourTexte;

            // Encode le texte pour qu'il soit utilisable dans une URL
            const jourTexteEncode = encodeURIComponent(jourTexte);

            // Met à jour le href de l'élément
            Jours[index].setAttribute('href', `confirmation.php?date=${jourTexteEncode}`);
        }
    });
}

window.onload = afficherDatesSemaine;

document.addEventListener('DOMContentLoaded', function() {
    const horaires = document.querySelectorAll('.Horraire a');
    horaires.forEach(horaire => {
        horaire.addEventListener('click', function() {
            horaires.forEach(h => h.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});
