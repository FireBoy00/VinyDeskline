// Personalize Page JavaScript
document.addEventListener("DOMContentLoaded", () => {
    const heightInput = document.getElementById("height");
    const ageInput = document.getElementById("age");
    const saveBtn = document.querySelector(".save-btn");
    const form = document.querySelector(".personalize-form");

    // Function to check if inputs are valid
    function validateInputs() {
        const heightValue = heightInput.value.trim();
        const ageValue = ageInput.value.trim();

        const heightValid =
            heightValue !== "" &&
            parseFloat(heightValue) >= 100 &&
            parseFloat(heightValue) <= 250;
        const ageValid =
            ageValue !== "" &&
            parseInt(ageValue) >= 18 &&
            parseInt(ageValue) <= 120;

        return heightValid && ageValid;
    }

    // Function to update button state
    function updateButtonState() {
        const isValid = validateInputs();
        saveBtn.disabled = !isValid;

        if (!isValid) {
            saveBtn.classList.add("disabled");
        } else {
            saveBtn.classList.remove("disabled");
        }
    }

    // Add event listeners to inputs
    heightInput.addEventListener("input", updateButtonState);
    ageInput.addEventListener("input", updateButtonState);

    // Prevent form submission if validation fails
    form.addEventListener("submit", (e) => {
        if (!validateInputs()) {
            e.preventDefault();
            alert(
                "Please enter both height (100-250 cm) and age (18+ years) to save your preferences. Otherwise, use the Skip button."
            );
        }
    });

    // Initial check on page load
    updateButtonState();
});
