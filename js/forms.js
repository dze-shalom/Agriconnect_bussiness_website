// AgriConnect Form Handler
class FormHandler {
    constructor() {
        this.initializeForms();
    }

    initializeForms() {
        // Newsletter form
        const newsletterForm = document.getElementById('newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', this.handleNewsletterSubmission.bind(this));
        }

        // Contact form
        const contactForm = document.getElementById('contact-form-main');
        if (contactForm) {
            contactForm.addEventListener('submit', this.handleContactSubmission.bind(this));
        }

        // Pilot form
        const pilotForm = document.getElementById('pilot-form');
        if (pilotForm) {
            pilotForm.addEventListener('submit', this.handlePilotSubmission.bind(this));
        }
    }

    async handleNewsletterSubmission(e) {
        e.preventDefault();
        const form = e.target;
        const email = form.querySelector('input[type="email"]').value;
        
        if (!this.validateEmail(email)) {
            this.showNotification('Please enter a valid email address', 'error');
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        this.setLoadingState(submitButton, 'Subscribing...');
        
        try {
            const response = await fetch('backend/newsletter.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, form_type: 'newsletter' })
            });
            
            if (response.ok) {
                this.showNotification('Successfully subscribed!', 'success');
                form.reset();
            } else {
                throw new Error('Subscription failed');
            }
        } catch (error) {
            this.showNotification('Something went wrong. Please try again.', 'error');
        } finally {
            this.resetButton(submitButton, originalText);
        }
    }

    validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    setLoadingState(button, text) {
        button.innerHTML = `<span class="loading"><span class="spinner"></span> ${text}</span>`;
        button.disabled = true;
    }

    resetButton(button, originalText) {
        button.innerHTML = originalText;
        button.disabled = false;
    }

    showNotification(message, type) {
        // Implementation from main.js showNotification method
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new FormHandler();
});