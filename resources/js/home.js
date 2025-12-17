const sensorData = [
    { id: 'temp', title: 'Temperature', value: '--', unit: '°C' },
    { id: 'light', title: 'Light', value: '--', unit: ' Lux' },
    { id: 'humid', title: 'Humidity', value: '--', unit: '%' },
];

let currentSlide = 0;
let paginationDotsContainer;
let sensorTitleElement;
let sensorValueElement;
let myPlotElement;

document.addEventListener('DOMContentLoaded', () => {
    paginationDotsContainer = document.getElementById('pagination-dots');
    sensorTitleElement = document.getElementById('sensor-title');
    sensorValueElement = document.getElementById('sensor-value');
    myPlotElement = document.getElementById('myPlot');
    
    document.getElementById('next-btn').addEventListener('click', () => navigate(1));
    document.getElementById('prev-btn').addEventListener('click', () => navigate(-1));

    renderCarousel();
    initializeChart();
});

function initializeChart() {
    const xArray = [50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150];
    const yArray = [7, 8, 8, 9, 9, 9, 10, 11, 14, 14, 15];

    if (window.Plotly && myPlotElement) {
        window.Plotly.newPlot(myPlotElement, [{
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
    }
}

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
        const displayValue = currentData.value !== '--' 
            ? currentData.value + currentData.unit 
            : '--';
        sensorValueElement.textContent = displayValue;
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

// Function to update sensor data from MQTT
window.updateSensorData = function(temperature, light, humidity ) {
    if (temperature !== null && temperature !== undefined) {
        sensorData[0].value = temperature;
    }
    if (light !== null && light !== undefined) {
        sensorData[1].value = light;
    }
    if (humidity !== null && humidity !== undefined) {
        sensorData[2].value = humidity;
    }
    
    // Update the display if we're currently viewing the changed sensor
    updateCarousel();
};

   // MQTT Connection for real-time sensor updates
    const client = mqtt.connect('ws://broker.hivemq.com:8000/mqtt');

    client.on('connect', () => {
        console.log('✓ MQTT connected to HiveMQ broker');
        client.subscribe('pico/sensors', (err) => {
            if (err) {
                console.error('✗ Subscription error:', err);
            } else {
                console.log('✓ Subscribed to pico/sensors topic');
            }
        });
    });

    client.on('error', (err) => {
        console.error('✗ MQTT connection error:', err);
    });

    client.on('reconnect', () => {
        console.log('↻ Reconnecting to MQTT broker...');
    });

    client.on('offline', () => {
        console.log('⚠ MQTT client offline');
    });

    client.on('message', (topic, message) => {
        try {
            const data = JSON.parse(message.toString());
            console.log('📨 Received sensor data:', data);

            // Update the carousel with new sensor values
            if (typeof window.updateSensorData === 'function') {
                window.updateSensorData(
                    data.temperature,
                    data.light,
                    data.humidity,
                );
            }

        } catch (err) {
            console.error('✗ Invalid MQTT message format:', err);
        }
    });

