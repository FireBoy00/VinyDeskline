document.addEventListener("DOMContentLoaded", function () {
    // Admin stats API endpoint (used to populate the overview cards below)
    const STATS_API = "/admin/desks/stats";

    // Cache DOM elements for live updates
    const statTotal = document.getElementById("stat-total");
    const statLastUpdated = document.getElementById("stat-last-updated");
    const refreshIcon = document.getElementById("refresh-icon");

    // Countdown from 10 to 0
    let countdown = 10;
    let isFetching = false;

    // Update the overview card values with data from the API. Uses a fixed polling interval so values feel "real-time".
    async function fetchAndUpdateStats() {
        // Prevent multiple simultaneous fetches
        if (isFetching) return;

        isFetching = true;

        // Add spin animation to refresh icon
        refreshIcon.classList.add("spinning");

        try {
            const res = await fetch(STATS_API, { cache: "no-store" });
            if (!res.ok) throw new Error("Failed to fetch dashboard stats");

            const json = await res.json();

            // Update stats with API data
            document.getElementById("stat-total-users").textContent =
                json.total_users ?? "N/A";
            document.getElementById("stat-total-desks").textContent =
                json.total_desks ?? "N/A";
            document.getElementById("stat-assigned").textContent =
                json.assigned ?? "N/A";
            document.getElementById("stat-sitting").textContent =
                json.sitting ?? "N/A";
            document.getElementById("stat-standing").textContent =
                json.standing ?? "N/A";
            document.getElementById("stat-active").textContent =
                json.active ?? "N/A";
        } catch (err) {
            console.warn("Dashboard stats fetch error:", err);
            // Show N/A when API is unavailable
            document.getElementById("stat-total-users").textContent = "N/A";
            document.getElementById("stat-total-desks").textContent = "N/A";
            document.getElementById("stat-assigned").textContent = "N/A";
            document.getElementById("stat-sitting").textContent = "N/A";
            document.getElementById("stat-standing").textContent = "N/A";
            document.getElementById("stat-active").textContent = "N/A";
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
        // Don't decrement if we're currently fetching
        if (isFetching) {
            return;
        }

        countdown--;

        // When countdown reaches 0, fetch and stay at 0
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
});

// Global chart container IDs
const CHART_CONTAINER_IDS = {
    timeline: "timelinePlot",
    standing: "piePlot",
    deskState: "deskStatePlot",
    dailyUsage: "dailyUsagePlot",

    temperature: "tempPlot",
    light: "lightPlot",
    humidity: "humidityPlot",
};

const NUM_USERS = 5;

const COLORS = [
    "#004F6E",
    "#0485B9",
    "#66B2D0",
    "#5B686E",
    "#C6DAE2",
    "#86A7B8",
    "#A9C4D3",
    "#396A87",
];
// --- Data Generation Functions ---

/**
 *  Sit/Stand Timeline for a single user
 */
function generateTimelineData(userIndex) {
    const data = { x: [], y: [] };
    const startTime = new Date();
    startTime.setHours(8, 0, 0, 0);
    let currentTime = new Date(startTime);

    const OFFSET = userIndex * 50;
    let currentHeight = 700 + OFFSET;
    let position = userIndex % 2 === 0 ? "Sitting" : "Standing";

    const totalDurationHours = 8;
    const totalSteps = (totalDurationHours * 60) / 15;

    for (let i = 0; i < totalSteps; i++) {
        currentTime = new Date(startTime.getTime() + i * 15 * 60000);
        data.x.push(new Date(currentTime));
        data.y.push(currentHeight + Math.floor(Math.random() * 5) - 2);

        if (i > 0 && i % (Math.floor(Math.random() * 3) + 3) === 0) {
            const newPosition = position === "Sitting" ? "Standing" : "Sitting";
            const targetBaseHeight = newPosition === "Standing" ? 1100 : 700;
            const targetHeight = targetBaseHeight + OFFSET;

            data.x.push(new Date(currentTime));
            data.y.push(currentHeight);

            currentTime = new Date(currentTime.getTime() + 1 * 60000);
            data.x.push(new Date(currentTime));
            data.y.push(targetHeight);

            currentHeight = targetHeight;
            position = newPosition;
        }
    }

    currentTime.setHours(16, 0, 0, 0);
    data.x.push(new Date(currentTime));
    data.y.push(currentHeight + Math.floor(Math.random() * 5) - 2);

    return { x: data.x, y: data.y };
}

/**
 * Standing Percentage Donut Chart
 */
function generateStandingPercentage() {
    const totalDesks = 100;
    const standingPercent = Math.floor(Math.random() * (50 - 15 + 1)) + 15;

    const standing = standingPercent;
    const sitting = 100 - standing;

    return { standing, sitting };
}

/**
 * Desk State Overview chart
 */
function generateDeskStateOverview() {
    const totalDesks = 200;

    let available = Math.floor(Math.random() * (70 - 40 + 1)) + 40;
    let occupied = Math.floor(Math.random() * (100 - 60 + 1)) + 60;
    let raised = Math.floor(Math.random() * (30 - 10 + 1)) + 10;
    let lowered = Math.floor(Math.random() * (30 - 10 + 1)) + 10;
    let unavailable = Math.floor(Math.random() * (15 - 5 + 1)) + 5;

    const currentTotal = available + occupied + raised + lowered + unavailable;

    if (currentTotal > totalDesks) {
        const overage = currentTotal - totalDesks;
        occupied = Math.max(0, occupied - overage);
    } else if (currentTotal < totalDesks) {
        const deficit = totalDesks - currentTotal;
        available += deficit;
    }

    return {
        Available: Math.round(available),
        Occupied: Math.round(occupied),
        Raised: Math.round(raised),
        Lowered: Math.round(lowered),
        Unavailable: Math.round(unavailable),
    };
}

// --- Plotly Render Functions ---

/**
 *  Sit/Stand Position Timeline (Step Line Chart)
 */
function renderSitStandTimelineChart() {
    const container = document.getElementById(CHART_CONTAINER_IDS.timeline);
    if (!container) return;

    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;

    const traces = [];
    for (let i = 0; i < NUM_USERS; i++) {
        const userData = generateTimelineData(i);
        const trace = {
            x: userData.x,
            y: userData.y,
            mode: "lines",
            name: `User ${i + 1}`,
            line: { color: COLORS[i % COLORS.length], width: 2, shape: "vh" },
            hovertemplate:
                "<b>User:</b> %{fullData.name}<br><b>Time:</b> %{x|%I:%M %p}<br><b>Height:</b> %{y} mm<extra></extra>",
        };
        traces.push(trace);
    }

    const layout = {
        width: containerWidth,
        height: containerHeight,
        title: "Sit/Stand Position Timeline Admin View",
        xaxis: {
            title: "Time of Day",
            type: "date",
            tickformat: "%I:%M %p",
            range: [new Date().setHours(7, 30), new Date().setHours(16, 30)],
        },
        yaxis: {
            title: "Height (mm)",
            tickvals: [700, 1100],
            ticktext: ["(Base 700)", "(Base 1100)"],
            range: [650, 1350],
        },
        margin: { t: 50, b: 50, l: 80, r: 20 },
        plot_bgcolor: "#dce5e9",
        paper_bgcolor: "#dce5e9",
        showlegend: true,
        legend: { orientation: "h", x: 0.5, y: 1.15, xanchor: "center" },
    };

    if (window.Plotly) {
        window.Plotly.newPlot(CHART_CONTAINER_IDS.timeline, traces, layout, {
            responsive: false,
            displayModeBar: false,
        });
    }
}

/**
 * Percentage of People Standing Donut Chart
 */
function renderStandingPercentageChart() {
    const TOTAL_DESKS = 100;

    const { standing, sitting } = generateStandingPercentage();

    const data = [
        {
            values: [standing, sitting],
            labels: ["Standing", "Sitting"],
            marker: { colors: [COLORS[1], COLORS[2]] },
            type: "pie",
            hole: 0.5,
            hoverinfo: "label+percent+value",
            textinfo: "none",
        },
    ];

    const layout = {
        title: "Percentage of People Standing",
        margin: { t: 30, b: 30, l: 30, r: 30 },
        showlegend: true,
        legend: { orientation: "h", y: -0.1, x: 0.5, xanchor: "center" },
        plot_bgcolor: "#dce5e9",
        paper_bgcolor: "#dce5e9",
        annotations: [
            {
                font: {
                    size: 16,
                    color: COLORS[0],
                },
                showarrow: false,
                text: `Total: ${TOTAL_DESKS}`,
                x: 0.5,
                y: 0.5,
            },
        ],
    };

    if (window.Plotly) {
        window.Plotly.newPlot(CHART_CONTAINER_IDS.standing, data, layout, {
            responsive: true,
            displayModeBar: false,
        });
    }
}

/**
 * Desk State Overview Donut Chart
 */
function renderDeskStateOverviewChart() {
    const states = generateDeskStateOverview();
    const labels = Object.keys(states);
    const values = Object.values(states);
    const totalDesks = values.reduce((sum, val) => sum + val, 0);

    const stateColors = {
        Available: COLORS[4],
        Occupied: COLORS[1],
        Raised: COLORS[0],
        Lowered: COLORS[5],
        Unavailable: COLORS[3],
    };

    const data = [
        {
            values: values,
            labels: labels,
            marker: { colors: labels.map((label) => stateColors[label]) },
            type: "pie",
            hole: 0.4,
            hoverinfo: "label+value+percent",
            textfont: { size: 12 },
        },
    ];

    const layout = {
        title: "Desk State Overview (Total: 200)",
        margin: { t: 50, b: 20, l: 20, r: 20 },
        showlegend: true,
        legend: { orientation: "h", y: -0.1, x: 0.5, xanchor: "center" },
        plot_bgcolor: "#dce5e9",
        paper_bgcolor: "#dce5e9",
        annotations: [
            {
                font: { size: 16, color: COLORS[0] },
                showarrow: false,
                text: `Total: ${totalDesks}`,
                x: 0.5,
                y: 0.5,
            },
        ],
    };

    if (window.Plotly) {
        window.Plotly.newPlot(CHART_CONTAINER_IDS.deskState, data, layout, {
            responsive: true,
            displayModeBar: false,
        });
    }
}

/**
 * Daily Desk Usage Duration stacked bar chart
 */
function generateDailyUsageDuration() {
    const days = ["Mon", "Tue", "Wed", "Thu", "Fri"];

    const usageData = {
        Sitting: [],
        Standing: [],
        Cleaning: [],
        Uniform: [],
    };

    days.forEach(() => {
        const totalWorkHours =
            Math.floor(Math.random() * (400 - 200 + 1)) + 200;
        const standingHours = Math.round((Math.random() * 80 + 40) * 10) / 10;
        const sittingHours =
            Math.round((totalWorkHours - standingHours) * 10) / 10;

        const cleaningMinutes = Math.floor(Math.random() * 60 * 3);
        const uniformMinutes = Math.floor(Math.random() * 60 * 1.5);

        usageData.Sitting.push(Math.max(0, sittingHours));
        usageData.Standing.push(standingHours);
        usageData.Cleaning.push(cleaningMinutes / 60);
        usageData.Uniform.push(uniformMinutes / 60);
    });

    return { days, usageData };
}

/**
 * Daily Desk Usage Duration Stacked Bar Chart
 */
function renderDailyUsageDurationChart() {
    const container = document.getElementById(CHART_CONTAINER_IDS.dailyUsage);
    if (!container) return;

    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;

    const { days, usageData } = generateDailyUsageDuration();
    const traces = [];

    const barColors = {
        Sitting: COLORS[0],
        Standing: COLORS[1],
        Cleaning: COLORS[5],
        Uniform: COLORS[6],
    };

    Object.keys(usageData).forEach((key) => {
        traces.push({
            x: days,
            y: usageData[key],
            name: key,
            type: "bar",
            marker: {
                color: barColors[key],
            },
            hovertemplate: `<b>${key} Duration:</b> %{y:.2f} hours<extra></extra>`,
        });
    });

    const layout = {
        width: containerWidth,
        height: containerHeight,
        title: "Daily Desk Usage Duration (Total Hours)",
        barmode: "stack",
        xaxis: {
            title: "Day of Week",
        },
        yaxis: {
            title: "Duration (Hours)",
        },
        margin: { t: 50, b: 50, l: 80, r: 20 },
        plot_bgcolor: "#dce5e9",
        paper_bgcolor: "#dce5e9",
        showlegend: true,
        legend: {
            orientation: "h",
            x: 0.5,
            y: 1.15,
            xanchor: "center",
        },
    };

    if (window.Plotly) {
        window.Plotly.newPlot(CHART_CONTAINER_IDS.dailyUsage, traces, layout, {
            responsive: true,
            displayModeBar: false,
        });
    }
}

// Generates randomized time-series data for environmental sensor readings,now including explicit min/max bounds for the output values.

function generateEnvironmentalData(
    startValue,
    fluctuation,
    drift,
    minBound,
    maxBound,
    numSteps = 60
) {
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

        const finalClampedValue = Math.max(
            minBound,
            Math.min(maxBound, currentValue)
        );

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
        hovertemplate:
            "<b>Time:</b> %{x|%I:%M %p}<br><b>Temp:</b> %{y:.1f}°C<extra></extra>",
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
        title: "Temperature vs. Time (°C)",
        xaxis: { title: "Time of Day", type: "date", tickformat: "%I:%M %p" },
        yaxis: { title: "Temperature (°C)", range: [15, 28] },
        margin: { t: 50, b: 50, l: 80, r: 20 },
        plot_bgcolor: "#dce5e9",
        paper_bgcolor: "#dce5e9",
        showlegend: true,
        legend: { orientation: "h", y: 1.15, xanchor: "center", x: 0.5 },
    };

    if (window.Plotly) {
        window.Plotly.newPlot(
            CHART_CONTAINER_IDS.temperature,
            [mainTrace, lowerBound, upperBound],
            layout,
            { responsive: true, displayModeBar: false }
        );
    }
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
        hovertemplate:
            "<b>Time:</b> %{x|%I:%M %p}<br><b>Light:</b> %{y:.0f} Lux<extra></extra>",
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

    if (window.Plotly) {
        window.Plotly.newPlot(CHART_CONTAINER_IDS.light, [trace], layout, {
            responsive: true,
            displayModeBar: false,
        });
    }
}

function renderHumidityChart() {
    const chartData = generateEnvironmentalData(45, 1, 0.5, 35, 55);

    const trace = {
        x: chartData.x,
        y: chartData.y,
        mode: "lines",
        name: "Humidity",
        line: { color: COLORS[0], width: 3 },
        hovertemplate:
            "<b>Time:</b> %{x|%I:%M %p}<br><b>Humidity:</b> %{y:.1f}%<extra></extra>",
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

    if (window.Plotly) {
        window.Plotly.newPlot(CHART_CONTAINER_IDS.humidity, [trace], layout, {
            responsive: true,
            displayModeBar: false,
        });
    }
}

// --- Pagination Logic ---

function setupEnvironmentalChartPagination() {
    const navButtons = document.querySelectorAll(
        "#environment-card .nav-button"
    );
    const chartContainers = document.querySelectorAll(
        "#environment-card .chart-plot"
    );

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
                        if (renderFunctions[container.id]) {
                            renderFunctions[container.id]();
                        }
                    }, 50);
                } else {
                    container.classList.remove("active-chart");
                    container.classList.add("hidden-chart");
                }
            });
        });
    });

    const initialActiveChart = document.querySelector(
        "#environment-card .active-chart"
    );
    if (initialActiveChart && window.Plotly) {
        window.Plotly.relayout(initialActiveChart.id, {});
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (typeof window.Plotly === "undefined") {
        console.error(
            "Plotly.js is not loaded. Please ensure it is linked in your HTML."
        );
        return;
    }

    renderSitStandTimelineChart();
    renderStandingPercentageChart();
    renderDeskStateOverviewChart();
    renderDailyUsageDurationChart();

    renderTemperatureChart();
    renderLightChart();
    renderHumidityChart();

    setupEnvironmentalChartPagination();

    // Fetch and populate schedule information
    fetch("/admin/next-schedules", {
        headers: {
            Accept: "application/json",
        },
    })
        .then((r) => r.json())
        .then((data) => {
            console.log(data);

            if (data.next_cleaning) {
                const c = data.next_cleaning;
                const dateStr =
                    c.frequency === "daily"
                        ? c.next_datetime
                        : `${c.date}T${c.start_time}`;
                const dt = new Date(dateStr);

                document.getElementById("cleaning_date").innerText = dt
                    .toISOString()
                    .slice(0, 10);
                document.getElementById("cleaning_time").innerText =
                    c.start_time;
            }

            if (data.next_uniform) {
                const u = data.next_uniform;
                const dateStr =
                    u.frequency === "daily"
                        ? u.next_datetime
                        : `${u.date}T${u.start_time}`;
                const dt = new Date(dateStr);

                document.getElementById("uniform_date").innerText = dt
                    .toISOString()
                    .slice(0, 10);
                document.getElementById("uniform_time").innerText =
                    u.start_time;
            }
        })
        .catch(console.error);
});
