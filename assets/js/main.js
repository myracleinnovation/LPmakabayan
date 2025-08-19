// Scroll functionality for main website
window.addEventListener('scroll', function () {
    const navbar = document.getElementById('scroll-navbar');
    const hero = document.querySelector('.position-relative.min-vh-100');
    if (!navbar || !hero) return;
    const heroBottom = hero.offsetTop + hero.offsetHeight - 80;
    if (window.scrollY > heroBottom) {
        navbar.classList.add('show-navbar');
    } else {
        navbar.classList.remove('show-navbar');
    }
});

const yearElement = document.getElementById('year');
if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
}

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modals with proper event handling
    const termsModal = document.getElementById('termsConditionModal');
    const privacyModal = document.getElementById('privacyStatementModal');
    
    if (termsModal) {
        const termsBootstrapModal = new bootstrap.Modal(termsModal);
        
        // Ensure proper cleanup when modal is hidden
        termsModal.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
        });
    }
    
    if (privacyModal) {
        const privacyBootstrapModal = new bootstrap.Modal(privacyModal);
        
        // Ensure proper cleanup when modal is hidden
        privacyModal.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
        });
    }
});