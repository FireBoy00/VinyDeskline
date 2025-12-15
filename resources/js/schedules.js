import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

document.addEventListener("DOMContentLoaded", () => {
    //initial_position=680, min_position=680, max_position=1320
    const uniformForm = document.getElementById("uniformForm");
    const cleaningForm = document.getElementById("cleaningForm");

    const uniformDateContainer = document.getElementById(
        "uniformDateContainer"
    );
    const uniformDateInput = document.getElementById("uniformDate");
    const uniformFrequencyRadios = document.querySelectorAll(
        'input[name="uniform-frequency"]'
    );

    const cleaningDateContainer = document.getElementById(
        "cleaningDateContainer"
    );
    const cleaningDateInput = document.getElementById("cleaningDate");
    const cleaningFrequencyRadios = document.querySelectorAll(
        'input[name="cleaning-frequency"]'
    );

    // Initialize time pickers
    flatpickr("#uniformStart", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
    });
    flatpickr("#uniformEnd", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
    });
    flatpickr("#cleaningStart", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
    });
    flatpickr("#cleaningEnd", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
    });

    // Height conversion display for uniform
    const uniformHeightInput = document.getElementById("uniform-height");
    const uniformHeightCmInput = document.getElementById(
        "uniform-height-cm-input"
    );

    uniformHeightInput.addEventListener("input", function () {
        const mm = parseFloat(this.value) || 0;
        const cm = (mm / 10).toFixed(1);
        uniformHeightCmInput.value = cm;
    });

    // Height conversion display for cleaning
    const cleaningHeightInput = document.getElementById("cleaning-height");
    const cleaningHeightCmInput = document.getElementById(
        "cleaning-height-cm-input"
    );

    cleaningHeightInput.addEventListener("input", function () {
        const mm = parseFloat(this.value) || 0;
        const cm = (mm / 10).toFixed(1);
        cleaningHeightCmInput.value = cm;
    });

    uniformFrequencyRadios.forEach((radio) => {
        radio.addEventListener("change", () => {
            if (radio.value === "once" && radio.checked) {
                uniformDateContainer.style.display = "block";
                flatpickr(uniformDateInput, {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                });
            } else if (radio.value === "multiple" && radio.checked) {
                uniformDateContainer.style.display = "block";
                flatpickr(uniformDateInput, {
                    dateFormat: "Y-m-d",
                    mode: "multiple",
                    minDate: "today",
                });
            } else {
                uniformDateContainer.style.display = "none";
            }
        });
    });

    cleaningFrequencyRadios.forEach((radio) => {
        radio.addEventListener("change", () => {
            if (radio.value === "once" && radio.checked) {
                cleaningDateContainer.style.display = "block";
                flatpickr(cleaningDateInput, {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                });
            } else if (radio.value === "multiple" && radio.checked) {
                cleaningDateContainer.style.display = "block";
                flatpickr(cleaningDateInput, {
                    dateFormat: "Y-m-d",
                    mode: "multiple",
                    minDate: "today",
                });
            } else {
                cleaningDateContainer.style.display = "none";
            }
        });
    });

    function sendSchedule(e, type) {
        e.preventDefault();

        const title = document.getElementById(`${type}-title`).value.trim();
        const start = document.getElementById(`${type}Start`).value;
        const end = document.getElementById(`${type}End`).value;
        const selectedRadio = document.querySelector(
            `input[name="${type}-frequency"]:checked`
        );
        const frequency = selectedRadio ? selectedRadio.value : "daily";
        const height = document.getElementById(`${type}-height`).value;

        let dates = [];
        const dateInput = document.getElementById(`${type}Date`);
        const fp = dateInput._flatpickr;
        if (fp) {
            dates = fp.selectedDates.map((d) => {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, "0");
                const day = String(d.getDate()).padStart(2, "0");
                return `${y}-${m}-${day}`;
            });
        }

        fetch("/admin/schedules", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                type,
                title,
                height,
                start_time: start,
                end_time: end,
                frequency,
                dates,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                const schedulesArray = data.schedules;
                const list = document.getElementById(type + "List");
                schedulesArray.forEach((schedule) => {
                    let displayDate = schedule.frequency;
                    if (schedule.frequency === "daily") {
                        displayDate = "Daily";
                    } else if (schedule.date) {
                        displayDate = schedule.date;
                    }

                    const div = document.createElement("div");

                    div.classList.add("current-schedule");
                    div.dataset.id = schedule.id;
                    div.innerHTML = `
                    <div class="current-schedule-info">
                        <span class="material-icons-round">schedule</span>
                        <span class="schedule-label">${schedule.title}:</span>
                        <span class="schedule-date">${displayDate}</span>
                        <span class="schedule-height">${schedule.height}mm</span>
                        <span class="schedule-time">${schedule.start_time} - ${schedule.end_time}</span>
                    </div>
                    <button class="delete-btn material-icons-round">delete</button>
                `;
                    list.appendChild(div);
                });
                e.target.reset();
            });
    }

    uniformForm.addEventListener("submit", (e) => sendSchedule(e, "uniform"));
    cleaningForm.addEventListener("submit", (e) => sendSchedule(e, "cleaning"));

    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("delete-btn")) {
            const item = e.target.closest(".current-schedule");
            const id = item.dataset.id;

            fetch(`/admin/schedules/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    console.log("Deleted:", data.message);
                    item.remove();
                });
        }
    });
});
