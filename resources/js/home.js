const sensorData = [
    { id: 'temp', title: 'Temperature', value: '19°C'},
    { id: 'humid', title: 'Humidity', value: '65%' },
    { id: 'light', title: 'Light', value: '750 Lux'},
];

let currentSlide = 0;
let cardsWrapper;
let paginationDotsContainer;
let sensorTitleElement;
let sensorValueElement;

document.addEventListener('DOMContentLoaded', () => {
    cardsWrapper = document.getElementById('cards-wrapper');
    paginationDotsContainer = document.getElementById('pagination-dots');
    sensorTitleElement = document.getElementById('sensor-title');
    sensorValueElement = document.getElementById('sensor-value');
    document.getElementById('next-btn').addEventListener('click', () => navigate(1));
    document.getElementById('prev-btn').addEventListener('click', () => navigate(-1));

    renderCarousel();
});

function renderCarousel() {
    if (cardsWrapper) {
        cardsWrapper.innerHTML = sensorData.map(() => '<div class="carousel-slide"></div>').join('');
    }

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