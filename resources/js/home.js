// Home page chart initialization
document.addEventListener('DOMContentLoaded', function () {
    const xArray = [50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150];
    const yArray = [7, 8, 8, 9, 9, 9, 10, 11, 14, 14, 15];

    Plotly.newPlot("myPlot", [{
        x: xArray,
        y: yArray,
        mode: "lines",
        line: { color: '#004F6E' }
    }], {
        xaxis: { title: "Square Meters" },
        yaxis: { title: "Price in Millions" },
        margin: { t: 20, b: 40, l: 60, r: 20 },
        plot_bgcolor: 'transparent',
        paper_bgcolor: 'transparent',
        showlegend: false
    });
});
