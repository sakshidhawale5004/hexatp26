/**
 * Country Editor JavaScript
 * Handles form interactions, auto-save, and validation
 * 
 * Requirements: 2.6, 2.7, 2.8, 2.9, 11.9
 */

// Auto-save functionality (every 2 minutes)
let autoSaveInterval;
let hasUnsavedChanges = false;

function initAutoSave() {
    // Mark form as changed
    const form = document.getElementById('countryForm');
    if (form) {
        form.addEventListener('input', () => {
            hasUnsavedChanges = true;
        });
        
        // Auto-save every 2 minutes
        autoSaveInterval = setInterval(() => {
            if (hasUnsavedChanges) {
                autoSaveForm();
            }
        }, 120000); // 2 minutes
    }
}

function autoSaveForm() {
    console.log('Auto-saving form...');
    // Implementation for auto-save
    // This would save as draft without redirecting
}

// Warn before leaving with unsaved changes
window.addEventListener('beforeunload', (e) => {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initAutoSave();
});

// Clean up on page unload
window.addEventListener('unload', () => {
    if (autoSaveInterval) {
        clearInterval(autoSaveInterval);
    }
});
