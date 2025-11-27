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

    // Update the overview card values with data from the API. Uses a fixed polling interval so values feel "real-time".
    async function fetchAndUpdateStats() {
        try {
            const res = await fetch(STATS_API, { cache: 'no-store' });
            if (!res.ok) throw new Error('Failed to fetch dashboard stats');

            const json = await res.json();

            if (json) {
                statTotal && (statTotal.textContent = String(json.total ?? 0));
                statOccupied && (statOccupied.textContent = String(json.occupied ?? 0));
                statAvailable && (statAvailable.textContent = String(json.available ?? 0));
                statRaised && (statRaised.textContent = String(json.raised ?? 0));
                statLowered && (statLowered.textContent = String(json.lowered ?? 0));
                statFaulty && (statFaulty.textContent = String(json.faulty ?? 0));
                if (json.last_updated && statLastUpdated) {
                    try {
                        // Display a friendly-localized time if possible
                        const t = new Date(json.last_updated);
                        statLastUpdated.textContent = `Last updated: ${t.toLocaleString()}`;
                    } catch (e) {
                        statLastUpdated.textContent = `Last updated: ${json.last_updated}`;
                    }
                }
            }
        } catch (err) {
            // On error, leave placeholders but log a warning — admin can refresh or check network.
            console.warn('Dashboard stats fetch error:', err);
        }
    }

    // First immediate fetch, then poll every 10 seconds
    fetchAndUpdateStats();
    const STATS_POLL_MS = 10000;
    setInterval(fetchAndUpdateStats, STATS_POLL_MS);

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
