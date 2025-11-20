// Chart initialization for dashboard
document.addEventListener('DOMContentLoaded', function () {
    // Line chart data and initialization
    const xArray = [50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150];
    const yArray = [7, 8, 8, 9, 9, 9, 10, 11, 14, 14, 15];

    if (window.Plotly) {
        window.Plotly.newPlot("myPlot", [{
            x: xArray,
            y: yArray,
            mode: "lines",
            line: { color: '#004F6E' }
        }], {
            autosize: true,
            xaxis: { title: "Square Meters" },
            yaxis: { title: "Price in Millions" },
            margin: { t: 20, b: 40, l: 60, r: 20 },
            plot_bgcolor: 'transparent',
            paper_bgcolor: 'transparent',
            showlegend: false
        }, { responsive: true });

        // Pie chart data and initialization
        const pieValues = [40, 30, 20, 10];
        const pieLabels = ['Sitting', 'Standing', 'Cleaning', 'Lowered'];
        const pieColors = ['#0485B9', '#004F6E', '#66B2D0', '#0485B9'];

        window.Plotly.newPlot('piePlot', [{
            values: pieValues,
            labels: pieLabels,
            type: 'pie',
            marker: { colors: pieColors, line: { color: '#ffffff', width: 2 } },
            hoverinfo: 'label+percent'
        }], {
            margin: { t: 10, b: 10, l: 10, r: 10 },
            showlegend: false,
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent'
        }, { responsive: true });
    }
});
