const sensorData = [
    { id: 'temp', title: 'Temperature', value: '19°C' },
    { id: 'humid', title: 'Humidity', value: '65%' },
    { id: 'light', title: 'Light', value: '750 Lux' },
];

let currentSlide = 0;
let paginationDotsContainer;
let sensorTitleElement;
let sensorValueElement;
let myPlotElement;

// Global chart container IDs
const chartContainers = {
    dailyUsage: 'myPlot',
    heightHistory: 'heightPlot'
};


// --- Data Randomization Functions ---

/**
 * Generates randomized duration data for a single day.
 * Includes Sitting, Standing, Cleaning, and Uniform.
 */
function generateDailyDurations() {
    const maxWorkMinutes = 480; // Total time (8 hours) to distribute

    let sitting = Math.floor(Math.random() * (maxWorkMinutes * 0.6 - maxWorkMinutes * 0.2 + 1)) + (maxWorkMinutes * 0.3);
    let standing = Math.floor(Math.random() * (maxWorkMinutes * 0.4 - maxWorkMinutes * 0. + 1)) + (maxWorkMinutes * 0.1);
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
        Uniform: Math.round(uniform) 
    };
}

/**
 * Generates time-series data for the Desk Height vs. Time line chart.
 * Simulates movement between Sitting (~700mm) and Standing (~1100mm) 
 */
function generateHeightHistory() {
    const data = { x: [], y: [] };

    const startTime = new Date();
    startTime.setHours(8, 0, 0, 0); // Start at 8:00 AM
    let currentTime = new Date(startTime);
    let currentHeight = 700;
    let position = 'Sitting';

    const totalDurationHours = 8;
    const totalSteps = totalDurationHours * 60 / 10; 

    for (let i = 0; i < totalSteps; i++) {
        currentTime = new Date(startTime.getTime() + i * 10 * 60000); 
        data.x.push(new Date(currentTime));
        data.y.push(currentHeight + Math.floor(Math.random() * 10) - 5); 

        if (i > 0 && i % 9 === 0) {
            const newPosition = (position === 'Sitting') ? 'Standing' : 'Sitting';
            const newHeight = (newPosition === 'Standing') ? 1100 : 700;
            const transitionDuration = 3 * 60000; // 3 minutes for transition

            const steps = 3;
            for (let j = 1; j <= steps; j++) {
                const stepTime = new Date(currentTime.getTime() + j / steps * transitionDuration);
                const stepHeight = currentHeight + (newHeight - currentHeight) * (j / steps);
                data.x.push(stepTime);
                data.y.push(stepHeight + Math.floor(Math.random() * 5) - 2);
            }

            currentTime = new Date(currentTime.getTime() + transitionDuration);
            data.x.push(currentTime);
            data.y.push(newHeight + Math.floor(Math.random() * 10) - 5);

            currentHeight = newHeight;
            position = newPosition;
            
            i += Math.floor(transitionDuration / (10 * 60000)); 
        }
    }

    return { x: data.x, y: data.y, annotations: [] }; 
}


// --- Plotly Render Functions ---

/**
 * Daily Desk Usage Duration (Stacked Bar Chart).
 */
function renderDailyUsageChart() {
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    const dataByDay = days.map(generateDailyDurations);

    const sittingTimes = dataByDay.map(d => d.Sitting);
    const standingTimes = dataByDay.map(d => d.Standing);
    const cleaningTimes = dataByDay.map(d => d.Cleaning);
    const uniformTimes = dataByDay.map(d => d.Uniform);

    const simpleHoverTemplate = "%{y}<extra></extra>";

    const sittingTrace = {
        x: days,
        y: sittingTimes,
        name: 'Sitting',
        type: 'bar',
        marker: { color: '#004F6E' }, 
        hovertemplate: simpleHoverTemplate
    };

    const standingTrace = {
        x: days,
        y: standingTimes,
        name: 'Standing',
        type: 'bar',
        marker: { color: '#0485B9' },
        hovertemplate: simpleHoverTemplate
    };

    const cleaningTrace = {
        x: days,
        y: cleaningTimes,
        name: 'Cleaning',
        type: 'bar',
        marker: { color: '#66B2D0' },
        hovertemplate: simpleHoverTemplate
    };

    const uniformTrace = { 
        x: days,
        y: uniformTimes, 
        name: 'Uniform', 
        type: 'bar',
        marker: { color: '#C6DAE2' }, 
        hovertemplate: simpleHoverTemplate
    };

    const data = [sittingTrace, standingTrace, cleaningTrace, uniformTrace]; 

    const layout = {
        barmode: 'stack',
        title: 'Daily Desk Usage Duration (Minutes)',
        xaxis: { title: 'Day of Week' },
        yaxis: { title: 'Duration (Minutes)' },
        margin: { t: 50, b: 50, l: 50, r: 20 },
        showlegend: false, 
        plot_bgcolor: '#dce5e9',
        paper_bgcolor: '#dce5e9'
    };

    if (window.Plotly) {
        window.Plotly.newPlot(chartContainers.dailyUsage, data, layout, { responsive: true, displayModeBar: false });
    }
}


/**
 * Desk Height vs. Time (Line Chart).
 */
function renderHeightHistoryChart() {
    const { x, y } = generateHeightHistory(); 

    const trace = {
        x: x,
        y: y,
        mode: 'lines',
        name: 'Desk Height',
        line: {
            color: '#000000ff',
            width: 3
        },
        hovertemplate: '<b>Time:</b> %{x|%I:%M %p}<br><b>Height:</b> %{y} mm<extra></extra>'
    };

    const layout = {
        title: 'Desk Height Across One Day',
        xaxis: {
            title: 'Time of Day',
            type: 'date',
            tickformat: '%I:%M %p' // Format time as HH:MM AM/PM
        },
        yaxis: {
            title: 'Height (mm)',
            range: [650, 1150]
        },
        annotations: [], 
        margin: { t: 50, b: 50, l: 50, r: 20 },
        plot_bgcolor: '#dce5e9',
        paper_bgcolor: '#dce5e9'
    };

    if (window.Plotly) {
        window.Plotly.newPlot(chartContainers.heightHistory, [trace], layout, { responsive: true, displayModeBar: false });
    }
}


// --- Carousel Functions 

function renderCarousel() {
    if (paginationDotsContainer) {
        paginationDotsContainer.innerHTML = sensorData.map((_, index) => 
            `<span class="dot" data-index="${index}"></span>`
        ).join('');

        document.querySelectorAll('.dot').forEach(dot => {
            dot.addEventListener('click', (e) => {
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

    document.querySelectorAll('.dot').forEach((dot, index) => {
        dot.classList.remove('active');
        if (index === currentSlide) {
            dot.classList.add('active');
        }
    });
}

function navigate(direction) {
    currentSlide += direction;
    updateCarousel();
}


// --- DOM Initialization ---

document.addEventListener('DOMContentLoaded', () => {
  
    paginationDotsContainer = document.getElementById('pagination-dots');
    sensorTitleElement = document.getElementById('sensor-title');
    sensorValueElement = document.getElementById('sensor-value');
    myPlotElement = document.getElementById('myPlot'); 

    document.getElementById('next-btn').addEventListener('click', () => navigate(1));
    document.getElementById('prev-btn').addEventListener('click', () => navigate(-1));

    if (typeof window.Plotly === 'undefined') {
        console.error("Plotly.js is not loaded. Please ensure it is linked in your HTML.");
        return;
    }

    renderDailyUsageChart();
    renderHeightHistoryChart();

    renderCarousel();
});