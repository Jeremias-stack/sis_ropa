import Chart from 'chart.js/auto';
import '../css/reportes.css';


document.addEventListener('DOMContentLoaded', () => {

    const dailyCanvas =
        document.getElementById('chartVentasDiarias');


    /*
     * Si no estamos en la página de Reportes,
     * no ejecutamos nada.
     */
    if (!dailyCanvas) {
        return;
    }


    /* =====================================================
       LEER JSON DESDE BLADE
       ===================================================== */

    const getJson = (id) => {

        const element =
            document.getElementById(id);

        if (!element) {
            return [];
        }

        try {

            return JSON.parse(
                element.textContent
            );

        } catch (error) {

            console.error(
                `Error leyendo ${id}`,
                error
            );

            return [];
        }

    };


    const dailyLabels =
        getJson('ventasDiariasLabels');

    const dailyData =
        getJson('ventasDiariasData');

    const weeklyLabels =
        getJson('ventasSemanalesLabels');

    const weeklyData =
        getJson('ventasSemanalesData');

    const monthlyLabels =
        getJson('ventasMensualesLabels');

    const monthlyData =
        getJson('ventasMensualesData');


    /* =====================================================
       FORMATO MONEDA
       ===================================================== */

    const money = (value) => {

        return new Intl.NumberFormat(
            'es-GT',
            {
                style: 'currency',
                currency: 'GTQ',
                minimumFractionDigits: 2
            }
        ).format(value);

    };


    /* =====================================================
       OPCIONES GENERALES
       ===================================================== */

    const createOptions = () => ({

        responsive: true,

        maintainAspectRatio: false,

        interaction: {
            intersect: false,
            mode: 'index'
        },

        plugins: {

            legend: {
                display: false
            },

            tooltip: {

                callbacks: {

                    label: (context) => {

                        const value =
                            context.parsed.y ?? 0;

                        return money(value);

                    }

                }

            }

        },

        scales: {

            y: {

                beginAtZero: true,

                ticks: {

                    callback: (value) => {

                        return `Q${Number(value).toLocaleString('es-GT')}`;

                    }

                }

            },

            x: {

                grid: {
                    display: false
                }

            }

        }

    });


    /* =====================================================
       VENTAS DIARIAS
       ===================================================== */

    new Chart(
        dailyCanvas,
        {
            type: 'line',

            data: {

                labels: dailyLabels,

                datasets: [
                    {
                        label: 'Ventas',
                        data: dailyData,

                        borderColor: '#18181b',

                        backgroundColor:
                            'rgba(24, 24, 27, 0.08)',

                        fill: true,

                        tension: 0.35,

                        pointRadius: 3,

                        pointHoverRadius: 5,

                        borderWidth: 2
                    }
                ]

            },

            options: createOptions()
        }
    );


    /* =====================================================
       VENTAS SEMANALES
       ===================================================== */

    const weeklyCanvas =
        document.getElementById(
            'chartVentasSemanales'
        );


    if (weeklyCanvas) {

        new Chart(
            weeklyCanvas,
            {
                type: 'bar',

                data: {

                    labels: weeklyLabels,

                    datasets: [
                        {
                            label: 'Ventas',

                            data: weeklyData,

                            backgroundColor:
                                '#27272a',

                            borderRadius: 7,

                            borderSkipped: false
                        }
                    ]

                },

                options: createOptions()
            }
        );

    }


    /* =====================================================
       VENTAS MENSUALES
       ===================================================== */

    const monthlyCanvas =
        document.getElementById(
            'chartVentasMensuales'
        );


    if (monthlyCanvas) {

        new Chart(
            monthlyCanvas,
            {
                type: 'bar',

                data: {

                    labels: monthlyLabels,

                    datasets: [
                        {
                            label: 'Ventas',

                            data: monthlyData,

                            backgroundColor:
                                '#52525b',

                            borderRadius: 7,

                            borderSkipped: false
                        }
                    ]

                },

                options: createOptions()
            }
        );

    }

});