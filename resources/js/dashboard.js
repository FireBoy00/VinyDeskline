// Navigation functionality and chart initialization
document.addEventListener('DOMContentLoaded', function () {
    // Chart data and initialization
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

    // Navigation logic
    const links = document.querySelectorAll('.nav-link[data-target]');
    const overall = document.getElementById('overall-container');
    const sections = {
        arrangement: document.getElementById('arrangement'),
        schedules: document.getElementById('schedules'),
        account: document.getElementById('account')
    };

    function hideAll() {
        if (overall) overall.style.display = 'none';
        Object.values(sections).forEach(section => { if (section) section.style.display = 'none'; });
    }

    function setActive(target) {
        links.forEach(link => link.classList.toggle('active', link.dataset.target === target));
    }

    function showTarget(target) {
        hideAll();
        setActive(target);
        if (target === 'overall') {
            if (overall) overall.style.display = '';
            return;
        }
        const sec = sections[target];
        if (sec) sec.style.display = '';
    }

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = this.dataset.target;
            if (!target) return;
            showTarget(target);
            // update URL hash without scrolling
            history.replaceState(null, '', '#' + target);
        });
    });

    // On load: if there's a hash that matches a section, show it. Otherwise show overall.
    const initial = location.hash ? location.hash.replace('#', '') : 'overall';
    if (initial === 'overall' || document.getElementById(initial)) {
        showTarget(initial);
    } else {
        showTarget('overall');
    }
});
