document.addEventListener('DOMContentLoaded', function () {
    const btnPrev = document.querySelector('button[data-nav="prev"]');
    const btnNext = document.querySelector('button[data-nav="next"]');
    const inputAnnee = document.querySelector('input[name="annee"]');
    const inputSemaine = document.querySelector('input[name="semaine"]');

    if (btnPrev && btnNext && inputAnnee && inputSemaine) {
        btnPrev.addEventListener('click', function (e) {
            e.preventDefault();
            let semaine = parseInt(inputSemaine.value);
            let annee = parseInt(inputAnnee.value);
            semaine -= 1;
            if (semaine < 1) {
                semaine = 52;
                annee -= 1;
            }
            inputSemaine.value = semaine;
            inputAnnee.value = annee;
            inputSemaine.form.submit();
        });

        btnNext.addEventListener('click', function (e) {
            e.preventDefault();
            let semaine = parseInt(inputSemaine.value);
            let annee = parseInt(inputAnnee.value);
            semaine += 1;
            if (semaine > 52) {
                semaine = 1;
                annee += 1;
            }
            inputSemaine.value = semaine;
            inputAnnee.value = annee;
            inputSemaine.form.submit();
        });
    }
});