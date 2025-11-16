/**
 * Main application JavaScript
 * Handles Alpine.js initialization and utility functions
 */

// Ensure Alpine loads
if (window.Alpine) {
    console.log('Alpine.js loaded successfully');
}

// Utility function for copying text to clipboard with fallback
function copyToClipboard(text) {
    if (navigator.clipboard) {
        return navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        return new Promise((resolve, reject) => {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            try {
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                resolve();
            } catch (err) {
                document.body.removeChild(textArea);
                reject(err);
            }
        });
    }
}

// Export for use in other modules
window.app = {
    copyToClipboard: copyToClipboard
};

// Initialize tooltips if Popper.js is available (optional enhancement)
document.addEventListener('DOMContentLoaded', function() {
    // Add any global initialization here
    console.log('Application initialized');

    // Accessibility: Skip to main content
    const skipLink = document.querySelector('.skip-to-main');
    if (skipLink) {
        skipLink.addEventListener('click', function(e) {
            e.preventDefault();
            const main = document.querySelector('main');
            if (main) {
                main.focus();
                main.tabIndex = -1;
            }
        });
    }

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('[data-alert-auto-dismiss]').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Performance monitoring (optional)
if (window.performance && window.performance.timing) {
    window.addEventListener('load', function() {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log('Page load time: ' + pageLoadTime + 'ms');
    });
}

export default app;
