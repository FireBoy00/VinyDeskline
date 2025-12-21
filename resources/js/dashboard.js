document.addEventListener("DOMContentLoaded", function () {
    // Admin stats API endpoint
    const METRICS_API = "/admin/dashboard/metrics";
    const STATS_API = "/admin/desks/stats"; // Keep for overview cards if needed, but we can use metrics data

    // Cache DOM elements for live updates
    const statLastUpdated = document.getElementById("stat-last-updated");
    const refreshIcon = document.getElementById("refresh-icon");

    // Countdown from 10 to 0
    let countdown = 10;
    let isFetching = false;
    let selectedUserIds = [];

    // Update the overview card values with data from the API.
    async function fetchAndUpdateStats() {
        // Prevent multiple simultaneous fetches
        if (isFetching) return;

        isFetching = true;

        // Add spin animation to refresh icon
        refreshIcon.classList.add("spinning");

        try {
            let url = METRICS_API;
            if (selectedUserIds.length > 0) {
                url += `?user_ids=${selectedUserIds.join(',')}`;
            }

            const res = await fetch(url, { cache: "no-store" });
            if (!res.ok) throw new Error("Failed to fetch dashboard stats");

            const json = await res.json();

            // Update stats with API data
            document.getElementById("stat-total-users").textContent = json.total_users ?? "N/A";
            document.getElementById("stat-total-desks").textContent = json.total_desks ?? "N/A";
            document.getElementById("stat-assigned").textContent = json.assigned ?? "N/A";
            document.getElementById("stat-sitting").textContent = json.sitting ?? "N/A";
            document.getElementById("stat-standing").textContent = json.standing ?? "N/A";
            document.getElementById("stat-active").textContent = json.active ?? "N/A";

            // Update Charts
            renderSitStandTimelineChart(json.timeline);
            renderStandingPercentageChart(json.standingPercentage);
            renderDeskStateOverviewChart(json.deskState);
            renderDailyUsageDurationChart(json.dailyUsage);

            // Setup Dropdown if needed
            const items = document.getElementById('user-select-items');
            if (items && items.children.length === 0 && json.all_users) {
                setupUserDropdown(json.all_users);
            }

        } catch (err) {
            console.warn("Dashboard stats fetch error:", err);
            // Show N/A when API is unavailable
            document.getElementById("stat-total-users").textContent = "N/A";
            // ... other stats ...
        } finally {
            // Remove spin animation
            setTimeout(() => {
                refreshIcon.classList.remove("spinning");
            }, 600);

            // Reset countdown to 10 after fetch completes
            countdown = 10;
            statLastUpdated.textContent = `${countdown}s`;
            isFetching = false;
        }
    }

    // Countdown timer that ticks every second
    function tickCountdown() {
        if (isFetching) return;
        countdown--;
        if (countdown === 0) {
            statLastUpdated.textContent = "0s";
            fetchAndUpdateStats();
        } else if (countdown > 0) {
            statLastUpdated.textContent = `${countdown}s`;
        }
    }

    // First immediate fetch
    fetchAndUpdateStats();

    // Update countdown every second
    setInterval(tickCountdown, 1000);

    // --- Dropdown Logic ---
    function setupUserDropdown(users) {
        const select = document.getElementById('user-select');
        const selected = document.getElementById('user-select-selected');
        const items = document.getElementById('user-select-items');
        
        if (!select || !selected || !items) return;

        // Toggle dropdown
        selected.addEventListener('click', (e) => {
            e.stopPropagation();
            items.classList.toggle('hidden');
            select.classList.toggle('active');
        });
        
        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!select.contains(e.target)) {
                items.classList.add('hidden');
                select.classList.remove('active');
            }
        });
        
        // Populate items
        items.innerHTML = '';
        users.forEach(user => {
            const div = document.createElement('div');
            div.textContent = `${user.first_name} ${user.last_name}`;
            div.dataset.id = user.id;
            
            if (selectedUserIds.includes(user.id)) {
                div.classList.add('selected-multi');
            }
            
            div.addEventListener('click', (e) => {
                e.stopPropagation();
                const id = parseInt(div.dataset.id);
                
                if (selectedUserIds.includes(id)) {
                    selectedUserIds = selectedUserIds.filter(uid => uid !== id);
                    div.classList.remove('selected-multi');
                } else {
                    selectedUserIds.push(id);
                    div.classList.add('selected-multi');
                }
                
                updateSelectedText();
                fetchAndUpdateStats(); // Refetch with new filter
            });
            
            items.appendChild(div);
        });
    }

    function updateSelectedText() {
        const selected = document.getElementById('user-select-selected');
        if (selectedUserIds.length === 0) {
            selected.textContent = "Select Users...";
        } else {
            selected.textContent = `${selectedUserIds.length} User(s) Selected`;
        }
    }

    // --- Chart Render Functions ---
    
    const CHART_CONTAINER_IDS = {
        timeline: "timelinePlot",
        standing: "piePlot",
        deskState: "deskStatePlot",
        dailyUsage: "dailyUsagePlot",
        temperature: "tempPlot",
        light: "lightPlot",
        humidity: "humidityPlot",
    };

    const COLORS = [
        "#004F6E", "#0485B9", "#66B2D0", "#5B686E", "#C6DAE2", "#86A7B8", "#A9C4D3", "#396A87",
    ];

    function renderSitStandTimelineChart(timelineData) {
        const container = document.getElementById(CHART_CONTAINER_IDS.timeline);
        if (!container || !window.Plotly) return;

        const traces = timelineData.map((user, i) => ({
            x: user.x,
            y: user.y,
            mode: "lines",
            name: user.name,
            line: { color: COLORS[i % COLORS.length], width: 2, shape: "vh" },
            hovertemplate: "<b>User:</b> %{fullData.name}<br><b>Time:</b> %{x|%I:%M %p}<br><b>Height:</b> %{y} mm<extra></extra>",
        }));

        const layout = {
            title: "Sit/Stand Position Timeline Admin View",
            xaxis: {
                title: "Time of Day",
                type: "date",
                tickformat: "%I:%M %p",
                // range: [new Date().setHours(7, 30), new Date().setHours(16, 30)], // Let it auto-scale or set fixed range
            },
            yaxis: {
                title: "Height (mm)",
                range: [600, 1300],
            },
            margin: { t: 50, b: 50, l: 80, r: 20 },
            plot_bgcolor: "#dce5e9",
            paper_bgcolor: "#dce5e9",
            showlegend: true,
            legend: { orientation: "h", x: 0.5, y: 1.15, xanchor: "center" },
        };

        window.Plotly.newPlot(CHART_CONTAINER_IDS.timeline, traces, layout, {
            responsive: true,
            displayModeBar: false,
        });
    }

    function renderStandingPercentageChart(data) {
        if (!window.Plotly) return;
        
        const plotData = [{
            values: [data.standing, data.sitting],
            labels: ["Standing", "Sitting"],
            marker: { colors: [COLORS[1], COLORS[2]] },
            type: "pie",
            hole: 0.5,
            hoverinfo: "label+percent+value",
            textinfo: "none",
        }];

        const layout = {
            title: "Percentage of People Standing",
            margin: { t: 30, b: 30, l: 30, r: 30 },
            showlegend: true,
            legend: { orientation: "h", y: -0.1, x: 0.5, xanchor: "center" },
            plot_bgcolor: "#dce5e9",
            paper_bgcolor: "#dce5e9",
            annotations: [{
                font: { size: 16, color: COLORS[0] },
                showarrow: false,
                text: `Total: ${data.standing + data.sitting}`,
                x: 0.5, y: 0.5,
            }],
        };

        window.Plotly.newPlot(CHART_CONTAINER_IDS.standing, plotData, layout, {
            responsive: true,
            displayModeBar: false,
        });
    }

    function renderDeskStateOverviewChart(data) {
        if (!window.Plotly) return;
        
        const labels = Object.keys(data);
        const values = Object.values(data);
        const total = values.reduce((a, b) => a + b, 0);

        const stateColors = {
            Available: COLORS[4],
            Occupied: COLORS[1],
            Raised: COLORS[0],
            Lowered: COLORS[5],
            Unavailable: COLORS[3],
        };

        const plotData = [{
            values: values,
            labels: labels,
            marker: { colors: labels.map(l => stateColors[l] || COLORS[0]) },
            type: "pie",
            hole: 0.4,
            hoverinfo: "label+value+percent",
            textfont: { size: 12 },
        }];

        const layout = {
            title: `Desk State Overview (Total: ${total})`,
            margin: { t: 50, b: 20, l: 20, r: 20 },
            showlegend: true,
            legend: { orientation: "h", y: -0.1, x: 0.5, xanchor: "center" },
            plot_bgcolor: "#dce5e9",
            paper_bgcolor: "#dce5e9",
            annotations: [{
                font: { size: 16, color: COLORS[0] },
                showarrow: false,
                text: `Total: ${total}`,
                x: 0.5, y: 0.5,
            }],
        };

        window.Plotly.newPlot(CHART_CONTAINER_IDS.deskState, plotData, layout, {
            responsive: true,
            displayModeBar: false,
        });
    }

    function renderDailyUsageDurationChart(data) {
        if (!window.Plotly) return;
        
        const traces = [];
        const barColors = {
            Sitting: COLORS[0],
            Standing: COLORS[1],
            Cleaning: COLORS[5],
            Uniform: COLORS[6],
        };

        Object.keys(data.usageData).forEach(key => {
            traces.push({
                x: data.days,
                y: data.usageData[key],
                name: key,
                type: "bar",
                marker: { color: barColors[key] || COLORS[0] },
                hovertemplate: `<b>${key} Duration:</b> %{y:.2f} hours<extra></extra>`,
            });
        });

        const layout = {
            title: "Daily Desk Usage Duration (Total Hours)",
            barmode: "stack",
            xaxis: { title: "Day of Week" },
            yaxis: { title: "Duration (Hours)" },
            margin: { t: 50, b: 50, l: 80, r: 20 },
            plot_bgcolor: "#dce5e9",
            paper_bgcolor: "#dce5e9",
            showlegend: true,
            legend: { orientation: "h", x: 0.5, y: 1.15, xanchor: "center" },
        };

        window.Plotly.newPlot(CHART_CONTAINER_IDS.dailyUsage, traces, layout, {
            responsive: true,
            displayModeBar: false,
        });
    }

    // --- Environmental Data (Kept as is) ---
    function generateEnvironmentalData(startValue, fluctuation, drift, minBound, maxBound, numSteps = 60) {
        const data = { x: [], y: [] };
        const startTime = new Date();
        startTime.setHours(8, 0, 0, 0);
        let currentValue = startValue;
        const totalDurationMinutes = 8 * 60;
        const stepSizeMs = (totalDurationMinutes / numSteps) * 60000;

        for (let i = 0; i <= numSteps; i++) {
            let currentTime = new Date(startTime.getTime() + i * stepSizeMs);
            currentValue += Math.random() * 2 * fluctuation - fluctuation;
            currentValue += drift * (i / numSteps);
            const finalClampedValue = Math.max(minBound, Math.min(maxBound, currentValue));
            data.x.push(currentTime);
            data.y.push(finalClampedValue);
        }
        return data;
    }

    function renderTemperatureChart() {
        const chartData = generateEnvironmentalData(22, 0.5, 1.5, 18, 25);
        const mainTrace = {
            x: chartData.x,
            y: chartData.y,
            mode: "lines",
            name: "Temperature",
            line: { color: COLORS[1], width: 3 },
            hovertemplate: "<b>Time:</b> %{x|%I:%M %p}<br><b>Temp:</b> %{y:.1f}C<extra></extra>",
        };
        const lowerBound = {
            x: chartData.x,
            y: chartData.x.map(() => 21),
            mode: "lines",
            name: "Min Recommended",
            line: { color: COLORS[3], width: 1, dash: "dash" },
            hoverinfo: "none",
        };
        const upperBound = {
            x: chartData.x,
            y: chartData.x.map(() => 23),
            mode: "lines",
            name: "Max Recommended",
            line: { color: COLORS[3], width: 1, dash: "dash" },
            hoverinfo: "none",
        };
        const layout = {
            title: "Temperature vs. Time (C)",
            xaxis: { title: "Time of Day", type: "date", tickformat: "%I:%M %p" },
            yaxis: { title: "Temperature (C)", range: [15, 28] },
            margin: { t: 50, b: 50, l: 80, r: 20 },
            plot_bgcolor: "#dce5e9",
            paper_bgcolor: "#dce5e9",
            showlegend: true,
            legend: { orientation: "h", y: 1.15, xanchor: "center", x: 0.5 },
        };
        if (window.Plotly) window.Plotly.newPlot(CHART_CONTAINER_IDS.temperature, [mainTrace, lowerBound, upperBound], layout, { responsive: true, displayModeBar: false });
    }

    function renderLightChart() {
        const chartData = generateEnvironmentalData(400, 30, 80, 300, 750);
        const trace = {
            x: chartData.x,
            y: chartData.y,
            fill: "to zero",
            type: "scatter",
            mode: "lines",
            name: "Light Intensity",
            line: { color: COLORS[2] },
            hovertemplate: "<b>Time:</b> %{x|%I:%M %p}<br><b>Light:</b> %{y:.0f} Lux<extra></extra>",
        };
        const layout = {
            title: "Light Intensity vs. Time (Lux)",
            xaxis: { title: "Time of Day", type: "date", tickformat: "%I:%M %p" },
            yaxis: { title: "Light (Lux)", range: [0, 800] },
            margin: { t: 50, b: 50, l: 80, r: 20 },
            plot_bgcolor: "#dce5e9",
            paper_bgcolor: "#dce5e9",
            showlegend: false,
        };
        if (window.Plotly) window.Plotly.newPlot(CHART_CONTAINER_IDS.light, [trace], layout, { responsive: true, displayModeBar: false });
    }

    function renderHumidityChart() {
        const chartData = generateEnvironmentalData(45, 1, 0.5, 35, 55);
        const trace = {
            x: chartData.x,
            y: chartData.y,
            mode: "lines",
            name: "Humidity",
            line: { color: COLORS[0], width: 3 },
            hovertemplate: "<b>Time:</b> %{x|%I:%M %p}<br><b>Humidity:</b> %{y:.1f}%<extra></extra>",
        };
        const layout = {
            title: "Humidity vs. Time (%)",
            xaxis: { title: "Time of Day", type: "date", tickformat: "%I:%M %p" },
            yaxis: { title: "Humidity (%)", range: [30, 60] },
            margin: { t: 50, b: 50, l: 80, r: 20 },
            plot_bgcolor: "#dce5e9",
            paper_bgcolor: "#dce5e9",
            showlegend: false,
        };
        if (window.Plotly) window.Plotly.newPlot(CHART_CONTAINER_IDS.humidity, [trace], layout, { responsive: true, displayModeBar: false });
    }

    function setupEnvironmentalChartPagination() {
        const navButtons = document.querySelectorAll("#environment-card .nav-button");
        const chartContainers = document.querySelectorAll("#environment-card .chart-plot");
        const renderFunctions = {
            tempPlot: renderTemperatureChart,
            lightPlot: renderLightChart,
            humidityPlot: renderHumidityChart,
        };

        navButtons.forEach((button) => {
            button.addEventListener("click", function () {
                const targetId = this.getAttribute("data-chart");
                navButtons.forEach((btn) => btn.classList.remove("active"));
                this.classList.add("active");
                chartContainers.forEach((container) => {
                    if (container.id === targetId) {
                        container.classList.add("active-chart");
                        container.classList.remove("hidden-chart");
                        setTimeout(() => {
                            if (renderFunctions[container.id]) renderFunctions[container.id]();
                        }, 50);
                    } else {
                        container.classList.remove("active-chart");
                        container.classList.add("hidden-chart");
                    }
                });
            });
        });
        const initialActiveChart = document.querySelector("#environment-card .active-chart");
        if (initialActiveChart && window.Plotly) window.Plotly.relayout(initialActiveChart.id, {});
    }

    // Initialize Environmental Charts
    renderTemperatureChart();
    renderLightChart();
    renderHumidityChart();
    setupEnvironmentalChartPagination();

    // Fetch and populate schedule information
    fetch("/admin/next-schedules", { headers: { Accept: "application/json" } })
        .then((r) => r.json())
        .then((data) => {
            if (data.next_cleaning) {
                const c = data.next_cleaning;
                const dateStr = c.frequency === "daily" ? c.next_datetime : `${c.date}T${c.start_time}`;
                const dt = new Date(dateStr);
                document.getElementById("cleaning_date").innerText = dt.toISOString().slice(0, 10);
                document.getElementById("cleaning_time").innerText = c.start_time;
            }
            if (data.next_uniform) {
                const u = data.next_uniform;
                const dateStr = u.frequency === "daily" ? u.next_datetime : `${u.date}T${u.start_time}`;
                const dt = new Date(dateStr);
                document.getElementById("uniform_date").innerText = dt.toISOString().slice(0, 10);
                document.getElementById("uniform_time").innerText = u.start_time;
            }
        })
        .catch(console.error);
});
