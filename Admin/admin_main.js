const { createApp } = Vue;

createApp({
    data() {
        return {
            userName: "Jean Dupont",
            directorName: "Directeur: M. Martin",
            employees: [
                { id: 1, name: "Alice", color: "#FFCCCC" },
                { id: 2, name: "Bob", color: "#CCFFCC" },
                { id: 3, name: "Charlie", color: "#CCCCFF" }
            ],
            weekDays: ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
            hours: ["9h", "10h", "11h", "12h", "13h", "14h", "15h", "16h", "17h", "18h", "19h", "20h"],
            schedule: Array.from({ length: 13 }, () => Array.from({ length: 7 }, () => [])),
            form: {
                employeeId: null,
                dayIndex: null,
                startHourIndex: null,
                endHourIndex: null
            }
        };
    },
    methods: {
        getEmployeeColor(employeeId) {
            const employee = this.employees.find(emp => emp.id === employeeId);
            return employee ? employee.color : "#FFFFFF";
        },
        addSchedule() {
            const { employeeId, dayIndex, startHourIndex, endHourIndex } = this.form;

            if (
                employeeId === null || 
                dayIndex === null || 
                startHourIndex === null || 
                endHourIndex === null ||
                dayIndex < 0 || 
                dayIndex >= 7 || 
                startHourIndex < 0 || 
                startHourIndex >= 13 || 
                endHourIndex < 0 || 
                endHourIndex >= 13 ||
                startHourIndex > endHourIndex
            ) {
                alert("Les données saisies sont incorrectes. Veuillez réessayer.");
                return;
            }

            for (let i = startHourIndex; i <= endHourIndex; i++) {
                if (!this.schedule[i][dayIndex].includes(employeeId)) {
                    this.schedule[i][dayIndex] = [...this.schedule[i][dayIndex], employeeId];
                }
            }

            this.resetForm();
        },
        resetForm() {
            this.form = {
                employeeId: null,
                dayIndex: null,
                startHourIndex: null,
                endHourIndex: null
            };
        }
    }
}).mount('#app');
