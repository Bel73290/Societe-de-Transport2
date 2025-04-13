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
    const Jours = document.querySelectorAll('.grille .day a');

    dates.forEach((date, index) => {
        if (Jours[index]) {
            const jourTexte = date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
            });

            Jours[index].textContent = jourTexte;

            // Encodage du texte pour l'URL
            const dateEncodee = encodeURIComponent(jourTexte);
            Jours[index].href = `confirmation.php?date=${dateEncodee}`;
        }
    });
}



document.addEventListener('DOMContentLoaded', function() {
    afficherDatesSemaine();

    const horaires = document.querySelectorAll('.Horraire a');
    horaires.forEach(horaire => {
        horaire.addEventListener('click', function() {
            horaires.forEach(h => h.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});

