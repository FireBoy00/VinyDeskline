import { DeskInsightsService } from "./deskInsightsService.js";

const sensorData = [
    { id: "temp", title: "Temperature", value: "19°C" },
    { id: "humid", title: "Humidity", value: "65%" },
    { id: "light", title: "Light", value: "750 Lux" },
];

let currentSlide = 0;
let paginationDotsContainer;
let sensorTitleElement;
let sensorValueElement;
let myPlotElement;
let metricsData = [];
let insightsService = null;

// Global chart container IDs
const chartContainers = {
    dailyUsage: "myPlot",
    heightHistory: "heightPlot",
};

/**
 * Fetch desk metrics from the API
 */
async function fetchDeskMetrics() {
    try {
        const response = await fetch("/home/metrics");
        if (!response.ok) {
            console.error("Failed to fetch metrics:", response.statusText);
            // Fall back to generated data if metrics not available
            return generateFallbackMetrics();
        }

        const data = await response.json();
        if (data.success && data.metrics && data.metrics.length > 0) {
            metricsData = data.metrics;
            return metricsData;
        } else {
            console.warn("No metrics data available, using fallback data");
            return generateFallbackMetrics();
        }
    } catch (error) {
        console.error("Error fetching metrics:", error);
        return generateFallbackMetrics();
    }
}

/**
 * Generate fallback metrics data for demonstration
 */
function generateFallbackMetrics() {
    const metrics = [];
    const now = new Date();

    // Generate 30 days of metrics (one entry every 30 minutes)
    for (let day = 29; day >= 0; day--) {
        const date = new Date(now);
        date.setDate(date.getDate() - day);

        for (let hour = 8; hour < 17; hour++) {
            const entry = new Date(date);
            entry.setHours(hour);

            // 50/50 sitting vs standing, with some randomness
            const isSitting = Math.random() > 0.5;
            const height = isSitting
                ? Math.floor(Math.random() * 150) + 700 // 700-850mm
                : Math.floor(Math.random() * 200) + 1000; // 1000-1200mm

            metrics.push({
                height_mm: height,
                is_sitting: isSitting,
                recorded_at: entry.toISOString(),
                timestamp: entry.getTime() / 1000,
            });
        }
    }

    return metrics;
}

/**
 * Calculate daily sitting and standing durations from metrics
 */
function calculateDailyDurations(metrics) {
    const durations = {};

    // Group metrics by day
    const metricsByDay = {};
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

            // Assume each metric represents ~30 minutes if consecutive
            // Count transitions and time spent in each position
            dayMetrics.forEach((metric, index) => {
                const nextMetric = dayMetrics[index + 1];
                if (nextMetric) {
                    const timeDiff =
                        new Date(nextMetric.recorded_at) -
                        new Date(metric.recorded_at);
                    const minutes = timeDiff / (1000 * 60);

                    if (metric.is_sitting) {
                        sittingTime += minutes;
                    } else {
                        standingTime += minutes;
                    }
                }
            });

            durations[dayKey] = {
                Sitting: Math.round(sittingTime),
                Standing: Math.round(standingTime),
                Cleaning: 0,
                Uniform: 0,
            };
        });

    return durations;
}

/**
 * Generates time-series data for the Desk Height vs. Time line chart.
 * Uses real metrics data from the database
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
 * Generates randomized duration data for a single day (fallback).
 * Includes Sitting, Standing, Cleaning, and Uniform.
 */
function generateDailyDurations() {
    const maxWorkMinutes = 480; // Total time (8 hours) to distribute

    let sitting =
        Math.floor(
            Math.random() * (maxWorkMinutes * 0.6 - maxWorkMinutes * 0.2 + 1)
        ) +
        maxWorkMinutes * 0.3;
    let standing =
        Math.floor(
            Math.random() * (maxWorkMinutes * 0.4 - maxWorkMinutes * 0 + 1)
        ) +
        maxWorkMinutes * 0.1;
    let cleaning = Math.floor(Math.random() * 80);
    let uniform = Math.floor(Math.random() * 60);

    const total = sitting + standing + cleaning + uniform;
    if (total > maxWorkMinutes) {
        const ratio = maxWorkMinutes / total;
        sitting *= ratio;
        standing *= ratio;
        cleaning *= ratio;
        uniform *= ratio;
    }

    return {
        Sitting: Math.round(sitting),
        Standing: Math.round(standing),
        Cleaning: Math.round(cleaning),
        Uniform: Math.round(uniform),
    };
}

/**
 * Format minutes into human readable format
 * Returns: "0 min" (hidden), "45 min", "1h 30m", "2h 15m", etc.
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

/**
 * Helper function to create and render the daily usage chart
 */
function createAndRenderDailyChart(
    dayLabels,
    sittingTimes,
    standingTimes,
    isWeekly = false
) {
    // Create custom hover data with formatted times
    const customData = sittingTimes.map((sitting, idx) => ({
        sitting: sitting,
        standing: standingTimes[idx],
    }));

    const sittingHoverTemplate =
        "<b>Sitting:</b> " +
        customData.map((d) => formatDuration(d.sitting)).join("") +
        "<extra></extra>";
    const standingHoverTemplate =
        "<b>Standing:</b> " +
        customData.map((d) => formatDuration(d.standing)).join("") +
        "<extra></extra>";

    // Use Plotly's custom hover with customdata
    const sittingTrace = {
        x: dayLabels,
        y: sittingTimes,
        customdata: customData.map((d) => formatDuration(d.sitting)),
        name: "Sitting",
        type: "bar",
        marker: { color: "#004F6E" },
        hovertemplate: "<b>Sitting:</b> %{customdata}<extra></extra>",
    };

    const standingTrace = {
        x: dayLabels,
        y: standingTimes,
        customdata: customData.map((d) => formatDuration(d.standing)),
        name: "Standing",
        type: "bar",
        marker: { color: "#0485B9" },
        hovertemplate: "<b>Standing:</b> %{customdata}<extra></extra>",
    };

    const data = [sittingTrace, standingTrace];

    const title = isWeekly
        ? "Weekly Sitting vs Standing Time (Last 7 Days) in Minutes"
        : "Daily Sitting vs Standing Time (Minutes)";

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

    if (window.Plotly) {
        window.Plotly.newPlot(chartContainers.dailyUsage, data, layout, {
            responsive: true,
            displayModeBar: false,
        });
    }
}

/**
 * Daily Desk Usage Duration (Stacked Bar Chart).
 * Uses real metrics data from the database
 */
function renderDailyUsageChart() {
    // Calculate daily durations from metrics
    const dayDurations = calculateDailyDurations(metricsData);

    if (Object.keys(dayDurations).length === 0) {
        // Fallback to generated data
        const days = ["Mon", "Tue", "Wed", "Thu", "Fri"];
        const dataByDay = days.map(generateDailyDurations);

        const sittingTimes = dataByDay.map((d) => d.Sitting);
        const standingTimes = dataByDay.map((d) => d.Standing);

        createAndRenderDailyChart(days, sittingTimes, standingTimes, false);
        return;
    }

    // Get last 7 consecutive calendar days starting from Monday
    const today = new Date();
    const daysBackToMonday = (today.getDay() + 6) % 7; // How many days back to Monday
    const last7Days = [];
    const dayLabels = [];
    const dayNames = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(date.getDate() - daysBackToMonday + i);
        const dateKey = date.toISOString().split("T")[0]; // YYYY-MM-DD
        last7Days.push(dateKey);
        dayLabels.push(dayNames[i]);
    }

    // Get values for each day, or 0 if no data for that day
    const sittingTimes = last7Days.map((d) => dayDurations[d]?.Sitting ?? 0);
    const standingTimes = last7Days.map((d) => dayDurations[d]?.Standing ?? 0);

    createAndRenderDailyChart(dayLabels, sittingTimes, standingTimes, true);
}

/**
 * Desk Height vs. Time (Line Chart).
 * Uses real metrics data from the database
 */
function renderHeightHistoryChart() {
    let data;

    if (metricsData.length > 0) {
        data = generateHeightHistoryFromMetrics(metricsData);
    } else {
        // Fallback to empty data
        data = { x: [], y: [] };
    }

    if (data.x.length === 0) {
        // Generate fallback data if no real data available
        const startTime = new Date();
        startTime.setHours(8, 0, 0, 0);
        let currentHeight = 700;

        for (let i = 0; i < 48; i++) {
            const currentTime = new Date(startTime.getTime() + i * 10 * 60000);
            data.x.push(currentTime);
            data.y.push(currentHeight + Math.floor(Math.random() * 10) - 5);

            if (i > 0 && i % 12 === 0) {
                currentHeight = currentHeight === 700 ? 1100 : 700;
            }
        }
    }

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
            tickformat: "%I:%M %p", // Format time as HH:MM AM/PM
        },
        yaxis: {
            title: "Height (mm)",
            range: [650, 1150],
        },
        annotations: [],
        margin: { t: 50, b: 50, l: 50, r: 20 },
        plot_bgcolor: "#dce5e9",
        paper_bgcolor: "#dce5e9",
    };

    if (window.Plotly) {
        window.Plotly.newPlot(chartContainers.heightHistory, [trace], layout, {
            responsive: true,
            displayModeBar: false,
        });
    }
}

// --- Carousel Functions

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
        sensorValueElement.textContent = currentData.value;
    }

    document.querySelectorAll(".dot").forEach((dot, index) => {
        dot.classList.remove("active");
        if (index === currentSlide) {
            dot.classList.add("active");
        }
    });
}

/**
 * Render Daily Briefing
 */
function renderDailyBriefing() {
    const briefingElement = document.getElementById("daily-briefing-text");
    if (!briefingElement) return;

    if (insightsService && metricsData.length > 0) {
        const briefing = insightsService.generateDailyBriefing();
        briefingElement.textContent = briefing;
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

        // Check if observations contain "no data" messages
        const isNoDataObservation = (obs) => {
            return (
                obs.includes("No desk usage data recorded yet") ||
                obs.includes("No desk activity recorded today yet")
            );
        };

        // Render observations as cards
        observationsElement.innerHTML = feedback.observations
            .map(
                (obs) => `
                <div class="feedback-card-item${
                    isNoDataObservation(obs) ? " no-data" : ""
                }">
                    <div class="feedback-icon">📊</div>
                    <div class="feedback-text">${obs}</div>
                </div>
            `
            )
            .join("");

        // Render suggestions as cards
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

function navigate(direction) {
    currentSlide += direction;
    updateCarousel();
}

document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".save-icon");
    const deskId = document
        .querySelector('meta[name="desk-id"]')
        .getAttribute("content");
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    let height;

    buttons.forEach((button) => {
        button.addEventListener("click", async () => {
            const parentRow = button.closest(".pos-row");
            const positionIndex = button.getAttribute("data-position");
            if (positionIndex && parentRow) {
                const nameInput = parentRow.querySelector(".custom-name");
                const heightInput = parentRow.querySelector(".custom-height");

                const customName = nameInput.value;
                const customHeight = heightInput.value;

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
                                position_mm: customHeight * 10,
                            }),
                        }
                    );

                    const data = await response.json();

                    if (data.success) {
                        height = data.height / 10;
                        alert(`Height and name updated!`);
                    } else {
                        alert(
                            `Error: ${data.message}` || `Error updating height.`
                        );
                    }
                } catch (error) {
                    console.error("Error:", error);
                    alert("An error occurred while setting height.");
                }
            } else {
                height = button.getAttribute("data-height");
            }
            try {
                const response = await fetch(`/desks/${deskId}/set-height`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ position_mm: height * 10 }),
                });

                const data = await response.json();
                if (data.success) alert("Height updated!");
                else
                    alert(
                        `Desk Error : ${data.message}` ||
                            `Error updating height.`
                    );
                height = null;
            } catch (error) {
                console.error(error);
                alert("Desk error:  An error occurred while setting height.");
            }
        });
    });
});

// --- DOM Initialization ---

document.addEventListener("DOMContentLoaded", async () => {
    paginationDotsContainer = document.getElementById("pagination-dots");
    sensorTitleElement = document.getElementById("sensor-title");
    sensorValueElement = document.getElementById("sensor-value");
    myPlotElement = document.getElementById("myPlot");

    document
        .getElementById("next-btn")
        .addEventListener("click", () => navigate(1));
    document
        .getElementById("prev-btn")
        .addEventListener("click", () => navigate(-1));

    if (typeof window.Plotly === "undefined") {
        console.error(
            "Plotly.js is not loaded. Please ensure it is linked in your HTML."
        );
        return;
    }

    // Fetch real metrics data from API
    metricsData = await fetchDeskMetrics();

    // Initialize insights service with metrics data
    if (metricsData && metricsData.length > 0) {
        insightsService = new DeskInsightsService(metricsData);
    }

    // Render all components
    renderDailyUsageChart();
    renderHeightHistoryChart();
    renderDailyBriefing();
    renderFeedback();
    renderCarousel();
});
