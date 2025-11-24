/**
 * Account Page JavaScript
 * Handles user information and settings form submissions
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const SUCCESS_RELOAD_DELAY = 500; // Delay before reloading page after successful save

    // User Info Form Submission
    const userInfoForm = document.getElementById('user-info-form');
    const saveInfoBtn = userInfoForm.querySelector('.save-account-btn');
    const saveInfoBtnText = saveInfoBtn.querySelector('span:first-child');
    const saveInfoBtnIcon = saveInfoBtn.querySelector('.material-icons-round');
    
    userInfoForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable button and show loading state
        saveInfoBtn.disabled = true;
        saveInfoBtnText.textContent = 'Saving...';
        saveInfoBtnIcon.textContent = 'hourglass_empty';
        
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
                saveInfoBtnText.textContent = 'Saved!';
                saveInfoBtnIcon.textContent = 'check_circle';
                setTimeout(() => window.location.reload(), SUCCESS_RELOAD_DELAY);
            } else {
                // Reset button state
                saveInfoBtn.disabled = false;
                saveInfoBtnText.textContent = 'Save Changes';
                saveInfoBtnIcon.textContent = 'check_circle';
                alert(result.message || 'Failed to update user information. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            // Reset button state
            saveInfoBtn.disabled = false;
            saveInfoBtnText.textContent = 'Save Changes';
            saveInfoBtnIcon.textContent = 'check_circle';
            alert(`An error occurred while updating your information: ${error.message}. Please try again. If the problem persists, contact support.`);
        }
    });

    // User Settings Form Submission
    const userSettingsForm = document.getElementById('user-settings-form');
    const saveSettingsBtn = userSettingsForm.querySelector('.save-settings-btn');
    const saveSettingsBtnText = saveSettingsBtn.querySelector('span:first-child');
    const saveSettingsBtnIcon = saveSettingsBtn.querySelector('.material-icons-round');
    
    userSettingsForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable button and show loading state
        saveSettingsBtn.disabled = true;
        saveSettingsBtnText.textContent = 'Saving...';
        saveSettingsBtnIcon.textContent = 'hourglass_empty';
        
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
                saveSettingsBtnText.textContent = 'Saved!';
                saveSettingsBtnIcon.textContent = 'check_circle';
                setTimeout(() => window.location.reload(), SUCCESS_RELOAD_DELAY);
            } else {
                // Reset button state
                saveSettingsBtn.disabled = false;
                saveSettingsBtnText.textContent = 'Save Settings';
                saveSettingsBtnIcon.textContent = 'check_circle';
                alert(result.message || 'Unable to update your settings. Please check your input and try again. If the problem persists, contact support.');
            }
        } catch (error) {
            console.error('Error:', error);
            // Reset button state
            saveSettingsBtn.disabled = false;
            saveSettingsBtnText.textContent = 'Save Settings';
            saveSettingsBtnIcon.textContent = 'check_circle';
            alert(`An error occurred while updating your settings: ${error.message}. Please try again. If the problem persists, contact support.`);
        }
    });

    // Reset Data Button
    const resetBtn = document.getElementById('reset-btn');
    const resetBtnText = resetBtn.querySelector('span:last-child');
    const resetBtnIcon = resetBtn.querySelector('.material-icons-round');
    
    resetBtn.addEventListener('click', async function() {
        if (!confirm('Are you sure you want to reset your height and age data? This action cannot be undone.')) {
            return;
        }

        // Disable button and show loading state
        resetBtn.disabled = true;
        resetBtnText.textContent = 'Resetting...';
        resetBtnIcon.textContent = 'hourglass_empty';

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
                resetBtnIcon.textContent = 'check_circle';
                setTimeout(() => window.location.reload(), SUCCESS_RELOAD_DELAY);
            } else {
                // Reset button state
                resetBtn.disabled = false;
                resetBtnText.textContent = 'Reset Data';
                resetBtnIcon.textContent = 'delete_forever';
                alert(result.message || 'Failed to reset data. Please try again. If the problem persists, contact support.');
            }
        } catch (error) {
            console.error('Error:', error);
            // Reset button state
            resetBtn.disabled = false;
            resetBtnText.textContent = 'Reset Data';
            resetBtnIcon.textContent = 'delete_forever';
            alert('Failed to reset data. Please try again. If the problem persists, contact support.');
        }
    });
});
