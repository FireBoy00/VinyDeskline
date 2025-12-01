// Chart initialization for dashboard
document.addEventListener('DOMContentLoaded', function () {
    // Admin stats API endpoint (used to populate the overview cards below)
    const STATS_API = '/admin/desks/stats';

    // Cache DOM elements for live updates
    const statTotal = document.getElementById('stat-total');
    const statOccupied = document.getElementById('stat-occupied');
    const statAvailable = document.getElementById('stat-available');
    const statRaised = document.getElementById('stat-raised');
    const statLowered = document.getElementById('stat-lowered');
    const statFaulty = document.getElementById('stat-faulty');
    const statLastUpdated = document.getElementById('stat-last-updated');
    const refreshIcon = document.getElementById('refresh-icon');

    // Countdown from 10 to 0
    let countdown = 10;

    // Update the overview card values with data from the API. Uses a fixed polling interval so values feel "real-time".
    async function fetchAndUpdateStats() {
        // Add spin animation to refresh icon
        refreshIcon.classList.add('spinning');
        
        try {
            const res = await fetch('/admin/desks/stats', { cache: 'no-store' });
            if (!res.ok) throw new Error('Failed to fetch dashboard stats');

            const json = await res.json();

            document.getElementById("stat-total").textContent = json.total_users;
            document.getElementById("stat-seated").textContent = json.seated;
            document.getElementById("stat-standing").textContent = json.standing;
            document.getElementById("stat-active").textContent = json.active;
            document.getElementById("stat-cleaning").textContent = json.cleaning;
            document.getElementById("stat-idle").textContent = json.idle;

            // Reset countdown to 10 after successful fetch
            countdown = 10;
            statLastUpdated.textContent = `${countdown}s`;

        } catch (err) {
            console.warn("Dashboard stats fetch error:", err);
        }
        
        // Remove spin animation after a short delay
        setTimeout(() => {
            refreshIcon.classList.remove('spinning');
        }, 600);
    }

    // Countdown timer that ticks every second
    function tickCountdown() {
        countdown--;
        
        // When countdown reaches 0, fetch immediately and reset
        if (countdown === 0) {
            fetchAndUpdateStats();
        } else {
            statLastUpdated.textContent = `${countdown}s`;
        }
    }

    // First immediate fetch
    fetchAndUpdateStats();
    
    // Update countdown every second
    setInterval(tickCountdown, 1000);

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
