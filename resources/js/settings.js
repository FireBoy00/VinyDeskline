/**
 * Settings Page JavaScript
 * Handles user information form submission and reset data functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const SUCCESS_RELOAD_DELAY = 500; // Delay before reloading page after successful save

    // User Info Form Submission
    const userInfoForm = document.getElementById('user-info-form');
    const saveBtn = userInfoForm.querySelector('.save-btn');
    const saveBtnText = saveBtn.querySelector('span');
    
    userInfoForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable button and show loading state
        saveBtn.disabled = true;
        saveBtnText.textContent = 'Saving...';
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch(this.dataset.updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                // Show success state briefly
                saveBtnText.textContent = 'Saved!';
                setTimeout(() => window.location.reload(), SUCCESS_RELOAD_DELAY);
            } else {
                // Reset button state
                saveBtn.disabled = false;
                saveBtnText.textContent = 'Save';
                alert(result.message || 'Failed to update information. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            // Reset button state
            saveBtn.disabled = false;
            saveBtnText.textContent = 'Save';
            alert(`An error occurred while updating your information: ${error.message}. Please try again.`);
        }
    });

    // Reset Data Button
    const resetBtn = document.getElementById('reset-btn');
    const resetBtnText = resetBtn.querySelector('span');
    
    resetBtn.addEventListener('click', async function() {
        if (!confirm('Are you sure you want to reset your data? This will clear your height and age information. This action cannot be undone.')) {
            return;
        }

        // Disable button and show loading state
        resetBtn.disabled = true;
        resetBtnText.textContent = 'Resetting...';

        try {
            const response = await fetch(this.dataset.resetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok) {
                // Show success state briefly
                resetBtnText.textContent = 'Reset!';
                setTimeout(() => window.location.reload(), SUCCESS_RELOAD_DELAY);
            } else {
                // Reset button state
                resetBtn.disabled = false;
                resetBtnText.textContent = 'Reset Data';
                alert(result.message || 'Failed to reset data. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            // Reset button state
            resetBtn.disabled = false;
            resetBtnText.textContent = 'Reset Data';
            alert('Failed to reset data. Please try again.');
        }
    });
});
