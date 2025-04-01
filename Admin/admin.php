<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier Administrateur</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div id="app">
        <header>
            <div class="header-left">
                <img src="logo.png" alt="Logo de l'entreprise" class="logo">
                <h1>Entreprise XYZ</h1>
            </div>
            <div class="header-right">
                <p>Utilisateur connecté : {{ userName }}</p>
            </div>
        </header>

        <div class="main-content">
            <aside>
                <h2>{{ directorName }}</h2>
                <ul>
                    <li v-for="employee in employees" :key="employee.id" :style="{ color: employee.color }">
                        {{ employee.name }}
                    </li>
                </ul>
                <form class="schedule-form" @submit.prevent="addSchedule">
                    <h3>Ajouter une plage horaire :</h3>
                    <label for="employee">Employé :</label>
                    <select id="employee" v-model.number="form.employeeId" required>
                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                            {{ employee.name }}
                        </option>
                    </select>

                    <label for="day">Jour :</label>
                    <select id="day" v-model.number="form.dayIndex" required>
                        <option v-for="(day, dayIndex) in weekDays" :key="dayIndex" :value="dayIndex">
                            {{ day }}
                        </option>
                    </select>

                    <label for="start-hour">Début :</label>
                    <select id="start-hour" v-model.number="form.startHourIndex" required>
                        <option v-for="(hour, hourIndex) in hours" :key="hourIndex" :value="hourIndex">
                            {{ hour }}
                        </option>
                    </select>

                    <label for="end-hour">Fin :</label>
                    <select id="end-hour" v-model.number="form.endHourIndex" required>
                        <option v-for="(hour, hourIndex) in hours" :key="hourIndex" :value="hourIndex">
                            {{ hour }}
                        </option>
                    </select>

                    <button type="submit">Ajouter</button>
                </form>
                <div class="file-upload">
                    <h3>Importer des fichiers :</h3>
                    <form action="Excel.php" method="post" enctype="multipart/form-data">
                        <input type="file" name="excelFile" accept=".xls,.xlsx" required>
                        <button type="submit">Importer</button>
                    </form>
                </div>
            </aside>

            <main>
                <table class="calendar">
                    <thead>
                        <tr>
                            <th>Heures</th>
                            <th v-for="(day, dayIndex) in weekDays" :key="dayIndex">{{ day }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(hour, hourIndex) in hours" :key="hourIndex">
                            <td>{{ hour }}</td>
                            <td v-for="(day, dayIndex) in weekDays" :key="dayIndex" class="time-slot">
                                <div v-if="schedule[hourIndex] && schedule[hourIndex][dayIndex]"
                                     v-for="employeeId in schedule[hourIndex][dayIndex]"
                                     :key="employeeId"
                                     :style="{ backgroundColor: getEmployeeColor(employeeId) }"
                                     class="employee-indicator">
                                    {{ employeeId }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </main>
        </div>

        <footer>
            <p>© 2025 Entreprise XYZ - Tous droits réservés</p>
        </footer>
    </div>

    <script src="main.js" defer></script>
</body>
</html>