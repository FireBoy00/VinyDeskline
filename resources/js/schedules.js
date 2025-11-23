document.addEventListener("DOMContentLoaded", () =>
{
    const template = document.getElementById("scheduleTemplate");

    const saveUniformBtn= document.getElementById("saveUniformBtn");
    const saveCleaningBtn= document.getElementById("saveCleaningBtn");

    const uniformList =document.getElementById("uniformList");
    const cleaningList =document.getElementById("cleaningList");

    const uniformStart = document.getElementById("uniformStart");
    const cleaningStart = document.getElementById("cleaningStart");

    const uniformEnd = document.getElementById("uniformEnd");
    const cleaningEnd = document.getElementById("cleaningEnd");

    const uniformTitle = document.getElementById("uniform-title");
    const cleaningTitle = document.getElementById("cleaning-title");

    const uniformDateContainer = document.getElementById("uniformDateContainer");
    const uniformDateInput = document.getElementById("uniformDate");
    const uniformFrequencyRadios = document.querySelectorAll('input[name="uniform-frequency"]');

    const cleaningDateContainer = document.getElementById("cleaningDateContainer");
    const cleaningDateInput = document.getElementById("cleaningDate");
    const cleaningFrequencyRadios = document.querySelectorAll('input[name="cleaning-frequency"]');

    uniformFrequencyRadios.forEach(radio => {
        radio.addEventListener("change", () => {
            if (radio.value === "once" && radio.checked) {
                uniformDateContainer.style.display = "block";
                flatpickr(uniformDateInput, { dateFormat: "d-m-y" });
            } else {
                uniformDateContainer.style.display = "none";
            }
        });
    });

    const uniformCalendarIcon = document.querySelector('.radio-icon');
    uniformCalendarIcon.addEventListener("click", () => {
        uniformDateContainer.style.display = "block";
        uniformDateInput.focus();
    });

    cleaningFrequencyRadios.forEach(radio => {
        radio.addEventListener("change", () => {
            if (radio.value === "once" && radio.checked) {
                cleaningDateContainer.style.display = "block";
                flatpickr(cleaningDateInput, { dateFormat: "d-m-y" });
            } else {
                cleaningDateContainer.style.display = "none";
            }
        });
    });

    const cleaningCalendarIcon = document.querySelector('.radio-icon');
    cleaningCalendarIcon.addEventListener("click", () => {
        cleaningDateContainer.style.display = "block";
        cleaningDateInput.focus();
    });


    function addUniform() 
    {
        const title = uniformTitle.value.trim();
        const start = uniformStart.value;
        const end = uniformEnd.value;
        const date = uniformDateInput.value;

        let frequency = 'Every day';
        const selectedRadio = document.querySelector('input[name="uniform-frequency"]:checked');
        if (selectedRadio) {
            if (selectedRadio.value === 'once') {
                frequency = date;
            } else if (selectedRadio.value === 'daily') {
                frequency = 'Every day';
            } else if (selectedRadio.value === 'multiple') {
                frequency = 'Multiple days';
            }
        }
        
        const clone = template.content.cloneNode(true);

        const label = clone.querySelector(".schedule-label");
        const timeText = clone.querySelector(".schedule-time");
        const dateText = clone.querySelector(".schedule-date");
        const deleteBtn = clone.querySelector(".delete-btn");

        label.textContent = title + ":";
        timeText.textContent = `${start} - ${end}`;
        dateText.textContent = frequency;
        deleteBtn.addEventListener("click", () => 
        {
            deleteBtn.closest(".current-schedule").remove();
        });
        
        uniformList.appendChild(clone)

    }

    function addCleaning() 
    {
        const title = cleaningTitle.value.trim();
        const start = cleaningStart.value;
        const end = cleaningEnd.value;
        const date = cleaningDateInput.value;

        let frequency = 'Every day';
        const selectedRadio = document.querySelector('input[name="cleaning-frequency"]:checked');
        if (selectedRadio) {
            if (selectedRadio.value === 'once') {
                frequency = date;
            } else if (selectedRadio.value === 'daily') {
                frequency = 'Every day';
            } else if (selectedRadio.value === 'multiple') {
                frequency = 'Multiple days';
            }
        }
        
        const clone = template.content.cloneNode(true);

        const label = clone.querySelector(".schedule-label");
        const timeText = clone.querySelector(".schedule-time");
        const dateText = clone.querySelector(".schedule-date");
        const deleteBtn = clone.querySelector(".delete-btn");

        label.textContent = title + ":";
        timeText.textContent = `${start} - ${end}`;
        dateText.textContent = frequency;
        deleteBtn.addEventListener("click", () => 
        {
            deleteBtn.closest(".current-schedule").remove();
        });
        
        cleaningList.appendChild(clone)

    }
    
    saveUniformBtn.addEventListener("click", addUniform);
    saveCleaningBtn.addEventListener("click", addCleaning);

});

