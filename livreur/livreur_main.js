const { createApp } = Vue;

createApp({
    data() {
        return {
            deliveries: [
                { firstName: "Benjamin", lastName: "Pavard", address: "123 Rue Principale", status: null },
                { firstName: "Théo", lastName: "Hernandez", address: "456 Avenue des Fleurs", status: null },
                { firstName: "Eduardo", lastName: "Camavinga", address: "789 Boulevard Saint-Michel", status: null },
                { firstName: "N'Golo", lastName: "Kanté", address: "12 Rue de Paris", status: null },
                { firstName: "Kylian", lastName: "Mbappé", address: "34 Rue des Lilas", status: null },
                { firstName: "Adrien", lastName: "Rabiot", address: "56 Boulevard Carnot", status: null },
            ]
        };
    },
    methods: {
        setStatus(index, status) {
            this.deliveries[index].status = status;
        },
        endTour() {
            alert("Tournée terminée !");
        }
    }
}).mount('#app');