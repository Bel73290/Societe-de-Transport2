<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page Test Livraison</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .horaire-item {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Choisissez une date et une tranche horaire</h1>

    <!-- Sélection de la date -->
    <label for="date">Date :</label>
    <input type="date" id="selected_date" name="selected_date" required><br><br>

    <!-- Tranches horaires fictives -->
    <div id="tranches-horaires">
        <div class="horaire-item">
            <input type="radio" name="selected_horaire" value="1" id="h1">
            <label for="h1">08:00 - 10:00</label>
        </div>
        <div class="horaire-item">
            <input type="radio" name="selected_horaire" value="2" id="h2">
            <label for="h2">10:00 - 12:00</label>
        </div>
        <div class="horaire-item">
            <input type="radio" name="selected_horaire" value="3" id="h3">
            <label for="h3">14:00 - 16:00</label>
        </div>
    </div>

    <!-- Bouton de validation -->
    <button id="valider-btn">Valider</button>

    <script>
        document.getElementById("valider-btn").addEventListener("click", function() {
            const selectedHoraire = document.querySelector('input[name="selected_horaire"]:checked');
            const selectedDate = document.getElementById("selected_date").value;

            if (selectedHoraire && selectedDate) {
                const horaireId = selectedHoraire.value;
                const url = `confirmation.php?selected_horaire=${horaireId}&selected_date=${selectedDate}`;
                window.location.href = url;
            } else {
                alert("Veuillez sélectionner une date et une tranche horaire.");
            }
        });
    </script>
</body>
</html>
