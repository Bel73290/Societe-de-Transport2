document.addEventListener('DOMContentLoaded', () => {
    const backButton = document.getElementById('back-button');
    const calendarContainer = document.getElementById('calendar-container');
    const horaireContainer = document.getElementById('horaire-container');

    document.querySelectorAll('.date-btn').forEach(button => {
        button.addEventListener('click', function () {
            const selectedDate = this.getAttribute('data-date');

            calendarContainer.style.display = 'none';
            horaireContainer.style.display = 'block';
            backButton.style.display = 'inline-block';

            horaireContainer.innerHTML = '<p>Chargement des horaires...</p>';

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'selected_date=' + encodeURIComponent(selectedDate)
            })
            .then(response => response.text())
            .then(html => {
                horaireContainer.innerHTML = html;

                const form = document.getElementById('horaire-form');
                if (form) {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const selectedHoraire = document.querySelector('input[name="selected_horaire"]:checked');
                        if (selectedHoraire) {
                            fetch('', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: 'selected_horaire=' + encodeURIComponent(selectedHoraire.value) +
                                      '&selected_date=' + encodeURIComponent(selectedDate)
                            })
                            .then(response => response.text())
                            .then(() => {
                                backButton.click();
                            })
                            .catch(error => {
                                console.error('Erreur lors de la confirmation :', error);
                                //alert('Une erreur est survenue.');
                            });
                        } else {
                            alert('Veuillez sélectionner une tranche horaire.');
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des horaires :', error);
                horaireContainer.innerHTML = '<p>Une erreur est survenue. Veuillez réessayer plus tard.</p>';
            });
        });
    });

    backButton.addEventListener('click', () => {
        calendarContainer.style.display = 'block';
        horaireContainer.style.display = 'none';
        backButton.style.display = 'none';
    });
});
