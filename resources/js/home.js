import { DeskInsightsService } from "./deskInsightsService.js";

// --- Constants & State ---
const sensorData = [
    { id: "temp", title: "Temperature", value: "--", unit: "°C" },
    { id: "humid", title: "Humidity", value: "--", unit: "%" },
    { id: "light", title: "Light", value: "--", unit: " Lux" },
];

let currentSlide = 0;
let paginationDotsContainer;
let sensorTitleElement;
let sensorValueElement;
let metricsData = [];
let insightsService = null;

const chartContainers = {
    dailyUsage: "myPlot",
    heightHistory: "heightPlot",
};

// --- Data Fetching & Processing ---

/**
 * Fetch desk metrics from the API
 */
async function fetchDeskMetrics() {
    try {
        const response = await fetch("/home/metrics");
        if (!response.ok) {
            console.error("Failed to fetch metrics:", response.statusText);
            return [];
        }

        const data = await response.json();
        if (data.success && data.metrics && data.metrics.length > 0) {
            metricsData = data.metrics;
            return metricsData;
        } else {
            console.warn("No metrics data available");
            return [];
        }
    } catch (error) {
        console.error("Error fetching metrics:", error);
        return [];
    }
}

/**
 * Calculate daily sitting and standing durations from metrics
 */
function calculateDailyDurations(metrics) {
    const durations = {};
    const metricsByDay = {};

    // Group metrics by day
    metrics.forEach((metric) => {
        const date = new Date(metric.recorded_at);
        const dayKey = date.toISOString().split("T")[0]; // YYYY-MM-DD

        if (!metricsByDay[dayKey]) {
            metricsByDay[dayKey] = [];
        }
        metricsByDay[dayKey].push(metric);
    });

    // Calculate durations for each day
    Object.keys(metricsByDay)
        .sort()
        .forEach((dayKey) => {
            const dayMetrics = metricsByDay[dayKey];
            let sittingTime = 0;
            let standingTime = 0;

            dayMetrics.forEach((metric, index) => {
                const nextMetric = dayMetrics[index + 1];
                if (nextMetric) {
                    const timeDiff =
                        new Date(nextMetric.recorded_at) -
                        new Date(metric.recorded_at);
                    const minutes = timeDiff / (1000 * 60);

                    // Only count if the gap is reasonable (e.g., < 15 minutes)
                    if (minutes < 15) {
                        if (metric.is_sitting) {
                            sittingTime += minutes;
                        } else {
                            standingTime += minutes;
                        }
                    }
                }
            });

            durations[dayKey] = {
                Sitting: Math.round(sittingTime),
                Standing: Math.round(standingTime),
            };
        });

    return durations;
}

/**
 * Generates time-series data for the Desk Height vs. Time line chart.
 */
function generateHeightHistoryFromMetrics(metrics) {
    const data = { x: [], y: [] };

    if (!metrics || metrics.length === 0) {
        return data;
    }

    // Filter to last day only for better visualization
    const lastDay = new Date(metrics[metrics.length - 1].recorded_at);
    lastDay.setHours(0, 0, 0, 0);

    const todayMetrics = metrics.filter((metric) => {
        const metricDate = new Date(metric.recorded_at);
        metricDate.setHours(0, 0, 0, 0);
        return metricDate.getTime() === lastDay.getTime();
    });

    // If no data for today, use all available data
    const metricsToUse =
        todayMetrics.length > 0 ? todayMetrics : metrics.slice(-48);

    metricsToUse.forEach((metric) => {
        data.x.push(new Date(metric.recorded_at));
        data.y.push(metric.height_mm);
    });

    return data;
}

/**
 * Format minutes into human readable format
 */
function formatDuration(minutes) {
    if (!minutes || minutes === 0) {
        return "—";
    }

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    if (hours === 0) {
        return `${mins}m`;
    } else if (mins === 0) {
        return `${hours}h`;
    } else {
        return `${hours}h ${mins}m`;
    }
}

// --- Chart Rendering ---

/**
 * Helper function to create and render the daily usage chart
 */
function createAndRenderDailyChart(
    dayLabels,
    sittingTimes,
    standingTimes,
    isWeekly = false
) {
    if (!window.Plotly) return;

    const sittingTrace = {
        x: dayLabels,
        y: sittingTimes,
        customdata: sittingTimes.map((t) => formatDuration(t)),
        name: "Sitting",
        type: "bar",
        marker: { color: "#004F6E" },
        hovertemplate: "<b>Sitting:</b> %{customdata}<extra></extra>",
    };

    const standingTrace = {
        x: dayLabels,
        y: standingTimes,
        customdata: standingTimes.map((t) => formatDuration(t)),
        name: "Standing",
        type: "bar",
        marker: { color: "#0485B9" },
        hovertemplate: "<b>Standing:</b> %{customdata}<extra></extra>",
    };

    const title = isWeekly
        ? "Weekly Sitting vs Standing Time (Last 7 Days)"
        : "Daily Sitting vs Standing Time";

    const layout = {
        barmode: "stack",
        title: title,
        xaxis: { title: "Day of Week" },
        yaxis: { title: "Duration (Minutes)" },
        margin: { t: 50, b: 50, l: 50, r: 20 },
        showlegend: false,
        plot_bgcolor: "#dce5e9",
        paper_bgcolor: "#dce5e9",
    };

    window.Plotly.newPlot(
        chartContainers.dailyUsage,
        [sittingTrace, standingTrace],
        layout,
        {
            responsive: true,
            displayModeBar: false,
        }
    );
}

/**
 * Daily Desk Usage Duration (Stacked Bar Chart).
 */
function renderDailyUsageChart() {
    const dayDurations = calculateDailyDurations(metricsData);
    const dayNames = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

    if (Object.keys(dayDurations).length === 0) {
        createAndRenderDailyChart(
            dayNames,
            Array(7).fill(0),
            Array(7).fill(0),
            true
        );
        return;
    }

    // Get last 7 consecutive calendar days starting from Monday
    const today = new Date();
    const daysBackToMonday = (today.getDay() + 6) % 7;
    const last7Days = [];
    const dayLabels = [];

    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(date.getDate() - daysBackToMonday + i);
        const dateKey = date.toISOString().split("T")[0];
        last7Days.push(dateKey);
        dayLabels.push(dayNames[i]);
    }

    const sittingTimes = last7Days.map((d) => dayDurations[d]?.Sitting ?? 0);
    const standingTimes = last7Days.map((d) => dayDurations[d]?.Standing ?? 0);

    createAndRenderDailyChart(dayLabels, sittingTimes, standingTimes, true);
}

/**
 * Desk Height vs. Time (Line Chart).
 */
function renderHeightHistoryChart() {
    if (!window.Plotly) return;

    const data = generateHeightHistoryFromMetrics(metricsData);

    const trace = {
        x: data.x,
        y: data.y,
        mode: "lines",
        name: "Desk Height",
        line: {
            color: "#000000ff",
            width: 3,
        },
        hovertemplate:
            "<b>Time:</b> %{x|%I:%M %p}<br><b>Height:</b> %{y} mm<extra></extra>",
    };

    const layout = {
        title: "Desk Height Across One Day",
        xaxis: {
            title: "Time of Day",
            type: "date",
            tickformat: "%I:%M %p",
        },
        yaxis: {
            title: "Height (mm)",
            range: [680, 1320],
        },
        margin: { t: 50, b: 50, l: 50, r: 20 },
        plot_bgcolor: "#dce5e9",
        paper_bgcolor: "#dce5e9",
        annotations: [],
    };

    if (data.x.length === 0) {
        layout.annotations.push({
            text: "No data available for today",
            xref: "paper",
            yref: "paper",
            x: 0.5,
            y: 0.5,
            showarrow: false,
            font: {
                size: 16,
                color: "#666",
            },
        });
    }

    window.Plotly.newPlot(chartContainers.heightHistory, [trace], layout, {
        responsive: true,
        displayModeBar: false,
    });
}

// --- Carousel & UI Functions ---

function renderCarousel() {
    if (paginationDotsContainer) {
        paginationDotsContainer.innerHTML = sensorData
            .map(
                (_, index) => `<span class="dot" data-index="${index}"></span>`
            )
            .join("");

        document.querySelectorAll(".dot").forEach((dot) => {
            dot.addEventListener("click", (e) => {
                currentSlide = parseInt(e.target.dataset.index);
                updateCarousel();
            });
        });
    }

    updateCarousel();
}

function updateCarousel() {
    if (currentSlide < 0) {
        currentSlide = sensorData.length - 1;
    } else if (currentSlide >= sensorData.length) {
        currentSlide = 0;
    }

    const currentData = sensorData[currentSlide];

    if (sensorTitleElement) {
        sensorTitleElement.textContent = currentData.title;
    }
    if (sensorValueElement) {
        sensorValueElement.textContent =
            currentData.value !== "--"
                ? currentData.value + currentData.unit
                : "--";
    }

    document.querySelectorAll(".dot").forEach((dot, index) => {
        dot.classList.toggle("active", index === currentSlide);
    });
}

function navigate(direction) {
    currentSlide += direction;
    updateCarousel();
}

/**
 * Render Daily Briefing
 */
function renderDailyBriefing() {
    const briefingElement = document.getElementById("daily-briefing-text");
    if (!briefingElement) return;

    if (insightsService && metricsData.length > 0) {
        briefingElement.textContent = insightsService.generateDailyBriefing();
    } else {
        briefingElement.textContent =
            "Welcome! Start using your desk and we'll provide insights about your posture habits.";
    }
}

/**
 * Render Feedback observations and suggestions
 */
function renderFeedback() {
    const observationsElement = document.getElementById(
        "feedback-observations"
    );
    const suggestionsElement = document.getElementById("feedback-suggestions");

    if (!observationsElement || !suggestionsElement) return;

    if (insightsService && metricsData.length > 0) {
        const feedback = insightsService.generateFeedback();
        const isNoData = (obs) =>
            obs.includes("No desk usage data") ||
            obs.includes("No desk activity");

        observationsElement.innerHTML = feedback.observations
            .map(
                (obs) => `
                <div class="feedback-card-item${
                    isNoData(obs) ? " no-data" : ""
                }">
                    <div class="feedback-icon">📊</div>
                    <div class="feedback-text">${obs}</div>
                </div>
            `
            )
            .join("");

        suggestionsElement.innerHTML = feedback.suggestions
            .map(
                (sug) => `
                <div class="feedback-card-item">
                    <div class="feedback-icon">💡</div>
                    <div class="feedback-text">${sug}</div>
                </div>
            `
            )
            .join("");
    } else {
        observationsElement.innerHTML = `
            <div class="feedback-card-item no-data">
                <div class="feedback-icon">📊</div>
                <div class="feedback-text">No desk usage data recorded yet.</div>
            </div>
        `;
        suggestionsElement.innerHTML = `
            <div class="feedback-card-item">
                <div class="feedback-icon">💡</div>
                <div class="feedback-text">Start using your desk to receive personalized ergonomic recommendations.</div>
            </div>
        `;
    }
}

// --- MQTT & External Updates ---

/**
 * Update sensor data from MQTT or other sources
 */
window.updateSensorData = function (temperature, light, humidity) {
    if (temperature !== null && temperature !== undefined) {
        sensorData[0].value = temperature;
    }
    if (humidity !== null && humidity !== undefined) {
        sensorData[1].value = humidity;
    }
    if (light !== null && light !== undefined) {
        sensorData[2].value = light;
    }

    updateCarousel();
};

function initializeMqtt() {
    if (typeof mqtt === "undefined") return;

    const client = mqtt.connect("ws://broker.hivemq.com:8000/mqtt");

    client.on("connect", () => {
        console.log("✓ MQTT connected to HiveMQ broker");
        client.subscribe("pico/sensors", (err) => {
            if (err) {
                console.error("✗ Subscription error:", err);
            } else {
                console.log("✓ Subscribed to pico/sensors topic");
            }
        });
    });

    client.on("message", (topic, message) => {
        try {
            const data = JSON.parse(message.toString());
            window.updateSensorData(
                data.temperature,
                data.light,
                data.humidity
            );
        } catch (err) {
            console.error("✗ Invalid MQTT message format:", err);
        }
    });
}

// --- Desk Control ---

async function handleSaveIconClick(button) {
    const deskId = document
        .querySelector('meta[name="desk-id"]')
        ?.getAttribute("content");
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (!deskId || !csrfToken) return;

    const parentRow = button.closest(".pos-row");
    const positionIndex = button.getAttribute("data-position");
    let heightInMm;

    // Handle custom positions
    if (positionIndex && parentRow) {
        const nameInput = parentRow.querySelector(".custom-name");
        const heightInput = parentRow.querySelector(".custom-height");

        const customName = nameInput.value;
        const customHeightCm = heightInput.value;

        try {
            const response = await fetch(
                `/home/${deskId}/${positionIndex}/updateCustom`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({
                        index: positionIndex,
                        name: customName,
                        position_mm: customHeightCm * 10,
                    }),
                }
            );

            const data = await response.json();
            if (data.success) {
                heightInMm = data.height;
                alert(`Height and name updated!`);
            } else {
                alert(data.message || `Error updating height.`);
                return;
            }
        } catch (error) {
            console.error("Error:", error);
            return;
        }
    } else {
        // Handle optimal positions (standing/sitting)
        heightInMm = button.getAttribute("data-height");
    }

    // Set the desk height
    if (heightInMm) {
        try {
            const response = await fetch(`/desks/${deskId}/set-height`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    position_mm: parseInt(heightInMm),
                }),
            });

            const data = await response.json();
            if (data.success) {
                alert("Desk height set successfully!");
            } else {
                alert(data.message || `Error setting desk height.`);
            }
        } catch (error) {
            console.error(error);
        }
    }
}

// --- Main Initialization ---

document.addEventListener("DOMContentLoaded", async () => {
    // UI Elements
    paginationDotsContainer = document.getElementById("pagination-dots");
    sensorTitleElement = document.getElementById("sensor-title");
    sensorValueElement = document.getElementById("sensor-value");

    // Event Listeners
    document
        .getElementById("next-btn")
        ?.addEventListener("click", () => navigate(1));
    document
        .getElementById("prev-btn")
        ?.addEventListener("click", () => navigate(-1));

    document.querySelectorAll(".save-icon").forEach((button) => {
        button.addEventListener("click", () => handleSaveIconClick(button));
    });

    // Fetch real metrics data from API
    metricsData = await fetchDeskMetrics();

    // Initialize insights service
    if (metricsData && metricsData.length > 0) {
        insightsService = new DeskInsightsService(metricsData);
    }

    // Render all components
    renderCarousel();
    renderDailyUsageChart();
    renderHeightHistoryChart();
    renderDailyBriefing();
    renderFeedback();

    // Initialize MQTT
    initializeMqtt();
});
