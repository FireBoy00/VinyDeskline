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

document.addEventListener('DOMContentLoaded', function () {
    
    const buttons = document.querySelectorAll('.save-icon');
    const deskId = document.querySelector('meta[name="desk-id"]').getAttribute('content');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let height;

    buttons.forEach(button => {
        button.addEventListener('click', async () => {
   
            const parentRow = button.closest('.pos-row');
            const positionIndex = button.getAttribute('data-position');
            if (positionIndex && parentRow) {
                const nameInput = parentRow.querySelector('.custom-name');
                const heightInput = parentRow.querySelector('.custom-height');

                const customName = nameInput.value;
                const customHeight = heightInput.value;

                try {
                    const response = await fetch(`/home/${deskId}/${positionIndex}/updateCustom`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            index: positionIndex,
                            name: customName,
                            position_mm: customHeight *10,
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        height =(data.height)/10;
                        alert(`Height and name updated!`);
                        
                    } else {
                        alert(`Error: ${data.message}` || `Error updating height.`);
                    }
                } catch (error) {
                    console.error('Error:', error); 
                    alert('An error occurred while setting height.');
                }
            } 
            else
            {
                height = button.getAttribute('data-height');
            }
            try {
                const response = await fetch(`/desks/${deskId}/set-height`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ position_mm: height * 10 })
                });

                const data = await response.json();
                if (data.success) alert("Height updated!");
                else alert(`Desk Error : ${data.message}` || `Error updating height.`);
            } catch (error) {
                console.error(error);
                alert('Desk error:  An error occurred while setting height.');
            }
        });
    });
});