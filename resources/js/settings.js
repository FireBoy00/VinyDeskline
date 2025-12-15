/**
 * Settings Page JavaScript
 * Handles user information and settings form submissions
 */

document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const SUCCESS_RELOAD_DELAY = 500; // Delay before reloading page after successful save

    /**
     * Updates button state
     * @param {HTMLElement} button - The button element
     * @param {string} text - The text to display
     * @param {string} icon - The icon to display
     * @param {boolean} disabled - Whether the button should be disabled
     */
    function updateButtonState(button, text, icon, disabled) {
        const btnText = button.querySelector("span:first-child");
        const btnIcon = button.querySelector(".material-icons-round");

        if (btnText) btnText.textContent = text;
        if (btnIcon) btnIcon.textContent = icon;
        button.disabled = disabled;
    }

    /**
     * Display validation errors on form fields
     * @param {HTMLFormElement} form - The form element
     * @param {Object} errors - The validation errors object
     */
    function displayValidationErrors(form, errors) {
        // Clear any existing error messages first
        form.querySelectorAll(".error-text").forEach((el) => el.remove());
        form.querySelectorAll(".error-input").forEach((el) =>
            el.classList.remove("error-input")
        );

        // Display new errors
        Object.keys(errors).forEach((fieldName) => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                // Add error class to input
                field.classList.add("error-input");

                // Create and insert error message
                const errorDiv = document.createElement("div");
                errorDiv.className = "error-text";
                errorDiv.textContent = errors[fieldName][0]; // Show first error message
                field.parentElement.appendChild(errorDiv);
            }
        });
    }

    /**
     * Generic form submission handler
     * @param {HTMLFormElement} form - The form element
     * @param {HTMLElement} button - The submit button
     * @param {string} successText - Text to show on success
     * @param {string} defaultText - Default button text
     * @param {string} defaultIcon - Default button icon
     * @param {string} errorPrefix - Prefix for error messages
     */
    async function handleFormSubmit(
        form,
        button,
        successText,
        defaultText,
        defaultIcon,
        errorPrefix
    ) {
        // Clear previous validation errors
        form.querySelectorAll(".error-text").forEach((el) => el.remove());
        form.querySelectorAll(".error-input").forEach((el) =>
            el.classList.remove("error-input")
        );

        // Disable button and show loading state
        updateButtonState(button, "Saving...", "hourglass_empty", true);

        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch(form.dataset.updateUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (response.ok) {
                // Show success state briefly
                updateButtonState(button, successText, "check_circle", true);
                setTimeout(
                    () => window.location.reload(),
                    SUCCESS_RELOAD_DELAY
                );
            } else {
                // Reset button state
                updateButtonState(button, defaultText, defaultIcon, false);

                // Handle validation errors
                if (response.status === 422 && result.errors) {
                    displayValidationErrors(form, result.errors);
                } else {
                    alert(
                        result.message || `${errorPrefix}. Please try again.`
                    );
                }
            }
        } catch (error) {
            console.error("Error:", error);
            // Reset button state
            updateButtonState(button, defaultText, defaultIcon, false);
            alert(
                `An error occurred while ${errorPrefix.toLowerCase()}: ${
                    error.message
                }. Please try again. If the problem persists, contact support.`
            );
        }
    }

    // User Info Form Submission
    const userInfoForm = document.getElementById("user-info-form");
    const saveInfoBtn = userInfoForm.querySelector(".save-info-btn");

    userInfoForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        await handleFormSubmit(
            this,
            saveInfoBtn,
            "Saved!",
            "Save Changes",
            "check_circle",
            "Failed to update user information"
        );
    });

    // User Settings Form Submission
    const userSettingsForm = document.getElementById("user-settings-form");
    const saveSettingsBtn =
        userSettingsForm.querySelector(".save-settings-btn");

    userSettingsForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        await handleFormSubmit(
            this,
            saveSettingsBtn,
            "Saved!",
            "Save Settings",
            "check_circle",
            "Unable to update your settings"
        );
    });

    // Reset Data Button
    const resetBtn = document.getElementById("reset-btn");
    const resetBtnText = resetBtn.querySelector("span:last-child");
    const resetBtnIcon = resetBtn.querySelector(".material-icons-round");

    resetBtn.addEventListener("click", async function () {
        if (
            !confirm(
                "Are you sure you want to reset your height and age data? This action cannot be undone."
            )
        ) {
            return;
        }

        // Disable button and show loading state
        updateButtonState(resetBtn, "Resetting...", "hourglass_empty", true);

        try {
            const response = await fetch(this.dataset.resetUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (response.ok) {
                // Show success state briefly
                updateButtonState(resetBtn, "Reset!", "check_circle", true);
                setTimeout(
                    () => window.location.reload(),
                    SUCCESS_RELOAD_DELAY
                );
            } else {
                // Reset button state
                updateButtonState(
                    resetBtn,
                    "Reset Data",
                    "delete_forever",
                    false
                );
                alert(
                    result.message ||
                        `Failed to reset data. Please try again. If the problem persists, contact support.`
                );
            }
        } catch (error) {
            console.error("Error:", error);
            // Reset button state
            updateButtonState(resetBtn, "Reset Data", "delete_forever", false);
            alert(
                `Failed to reset data: ${error.message}. Please try again. If the problem persists, contact support.`
            );
        }
    });
});
