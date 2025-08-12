/**
 * AgriConnect Main JavaScript
 * Core functionality for mobile menu, form handling, and animations
 */

// Global AgriConnect object
window.AgriConnect = {
    // Initialize all functionality
    init: function() {
        this.initMobileMenu();
        this.initScrollAnimations();
        this.initFormHandling();
        this.initActiveNavigation();
        this.initSmoothScrolling();
        this.initHeaderScrollEffect();
        this.trackPageView();
    },

    // Mobile menu functionality
    initMobileMenu: function() {
        const menuToggle = document.getElementById('menu-toggle');
        const navLinks = document.getElementById('nav-links');
        const header = document.getElementById('header');
        
        if (!menuToggle || !navLinks) return;
        
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.classList.toggle('mobile-open');
            
            // Toggle icon
            const icon = menuToggle.querySelector('i');
            if (navLinks.classList.contains('mobile-open')) {
                icon.className = 'fas fa-times';
                document.body.style.overflow = 'hidden'; // Prevent scroll
            } else {
                icon.className = 'fas fa-bars';
                document.body.style.overflow = '';
            }
        });
        
        // Close menu when clicking nav links
        navLinks.addEventListener('click', function(e) {
            if (e.target.classList.contains('nav-link')) {
                navLinks.classList.remove('mobile-open');
                menuToggle.querySelector('i').className = 'fas fa-bars';
                document.body.style.overflow = '';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!header.contains(e.target) && navLinks.classList.contains('mobile-open')) {
                navLinks.classList.remove('mobile-open');
                menuToggle.querySelector('i').className = 'fas fa-bars';
                document.body.style.overflow = '';
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                navLinks.classList.remove('mobile-open');
                menuToggle.querySelector('i').className = 'fas fa-bars';
                document.body.style.overflow = '';
            }
        });
    },

    // Header scroll effect
    initHeaderScrollEffect: function() {
        const header = document.getElementById('header');
        if (!header) return;
        
        let lastScrollY = window.scrollY;
        let ticking = false;
        
        function updateHeader() {
            const scrollY = window.scrollY;
            
            if (scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            // Hide/show header on scroll
            if (scrollY > lastScrollY && scrollY > 200) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            
            lastScrollY = scrollY;
            ticking = false;
        }
        
        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', requestTick);
    },

    // Active navigation highlighting
    initActiveNavigation: function() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            const linkPage = link.getAttribute('href');
            
            if (linkPage === currentPage || 
                (currentPage === '' && linkPage === 'index.html') ||
                (currentPage === 'index.html' && linkPage === 'index.html')) {
                link.classList.add('active');
            }
        });
    },

    // Smooth scrolling for anchor links
    initSmoothScrolling: function() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                
                if (target) {
                    const headerHeight = document.getElementById('header').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    },

    // Scroll animations
    initScrollAnimations: function() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    
                    // Counter animation for stats
                    if (entry.target.classList.contains('stat-value')) {
                        this.animateCounter(entry.target);
                    }
                }
            });
        }, observerOptions);
        
        // Observe elements for animation
        const animateElements = document.querySelectorAll(
            '.timeline-item, .team-member, .feature-item, .service-item, .partner-card, .stat-value, .benefit-card'
        );
        
        animateElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
        
        // Add CSS for animate-in class
        const style = document.createElement('style');
        style.textContent = `
            .animate-in {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        `;
        document.head.appendChild(style);
    },

    // Counter animation for statistics
    animateCounter: function(element) {
        const target = parseInt(element.textContent.replace(/\D/g, ''));
        if (isNaN(target)) return;
        
        const suffix = element.textContent.replace(/[\d\s]/g, '');
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current) + suffix;
        }, 30);
    },

    // Form handling
    initFormHandling: function() {
        // Newsletter form
        const newsletterForm = document.getElementById('newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', this.handleNewsletterSubmission.bind(this));
        }
        
        // Pilot program form
        const pilotForm = document.getElementById('pilot-form');
        if (pilotForm) {
            pilotForm.addEventListener('submit', this.handlePilotSubmission.bind(this));
        }
        
        // Contact form
        const contactForm = document.getElementById('contact-form');
        if (contactForm) {
            contactForm.addEventListener('submit', this.handleContactSubmission.bind(this));
        }
    },

    // Newsletter submission
    handleNewsletterSubmission: async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const emailInput = form.querySelector('input[type="email"]');
        const originalText = submitButton.innerHTML;
        
        // Validation
        if (!emailInput.value || !this.validateEmail(emailInput.value)) {
            this.showNotification('Please enter a valid email address', 'error');
            return;
        }
        
        // Show loading
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
        submitButton.disabled = true;
        
        try {
            const response = await fetch('backend/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: emailInput.value,
                    form_type: 'newsletter'
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Successfully subscribed to our newsletter!', 'success');
                form.reset();
                this.trackEvent('Newsletter', 'Subscription', emailInput.value);
            } else {
                throw new Error(result.error || 'Subscription failed');
            }
            
        } catch (error) {
            console.error('Newsletter subscription error:', error);
            this.showNotification('Something went wrong. Please try again.', 'error');
        } finally {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    },

    // Pilot program submission
    handlePilotSubmission: async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitButton = document.getElementById('submit-button');
        const originalText = submitButton.innerHTML;
        
        // Collect form data
        const formData = new FormData(form);
        const applicationData = {
            form_type: 'pilot'
        };
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            applicationData[key] = value;
        }
        
        // Basic validation
        const requiredFields = ['firstName', 'lastName', 'email', 'phone', 'farmLocation', 'farmSize'];
        const missingFields = requiredFields.filter(field => !applicationData[field]);
        
        if (missingFields.length > 0) {
            this.showNotification('Please fill in all required fields', 'error');
            return;
        }
        
        if (!this.validateEmail(applicationData.email)) {
            this.showNotification('Please enter a valid email address', 'error');
            return;
        }
        
        // Show loading
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitButton.disabled = true;
        
        try {
            const response = await fetch('backend/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(applicationData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Hide form and show success message
                form.style.display = 'none';
                document.getElementById('success-message').style.display = 'block';
                document.getElementById('success-message').scrollIntoView({ behavior: 'smooth' });
                
                this.trackEvent('Pilot Program', 'Application Submitted', applicationData.email);
            } else {
                throw new Error(result.error || 'Application submission failed');
            }
            
        } catch (error) {
            console.error('Pilot application error:', error);
            this.showNotification('Something went wrong. Please try again or contact us directly.', 'error');
            
            // Restore button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    },

    // Contact form submission
    handleContactSubmission: async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Collect form data
        const formData = new FormData(form);
        const contactData = {
            form_type: 'contact'
        };
        
        for (let [key, value] of formData.entries()) {
            contactData[key] = value;
        }
        
        // Validation
        if (!contactData.name || !contactData.email || !contactData.message) {
            this.showNotification('Please fill in all required fields', 'error');
            return;
        }
        
        if (!this.validateEmail(contactData.email)) {
            this.showNotification('Please enter a valid email address', 'error');
            return;
        }
        
        // Show loading
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        submitButton.disabled = true;
        
        try {
            const response = await fetch('backend/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(contactData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Message sent successfully! We\'ll get back to you soon.', 'success');
                form.reset();
                this.trackEvent('Contact', 'Form Submitted', contactData.email);
            } else {
                throw new Error(result.error || 'Message sending failed');
            }
            
        } catch (error) {
            console.error('Contact form error:', error);
            this.showNotification('Something went wrong. Please try again or contact us directly.', 'error');
        } finally {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    },

    // Email validation
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Show notification
    showNotification: function(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.agriconnect-notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = `agriconnect-notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Add styles
        const styles = `
            .agriconnect-notification {
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
                padding: 16px;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                animation: slideInRight 0.3s ease;
            }
            
            .notification-success {
                background: #e8f5e9;
                border-left: 4px solid #4caf50;
                color: #2e7d32;
            }
            
            .notification-error {
                background: #ffebee;
                border-left: 4px solid #f44336;
                color: #c62828;
            }
            
            .notification-info {
                background: #e3f2fd;
                border-left: 4px solid #2196f3;
                color: #1565c0;
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            
            .notification-close {
                background: none;
                border: none;
                cursor: pointer;
                padding: 4px;
                margin-left: auto;
                opacity: 0.7;
                transition: opacity 0.2s;
            }
            
            .notification-close:hover {
                opacity: 1;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @media (max-width: 768px) {
                .agriconnect-notification {
                    left: 20px;
                    right: 20px;
                    max-width: none;
                }
            }
        `;
        
        // Add styles to document if not already added
        if (!document.getElementById('notification-styles')) {
            const styleSheet = document.createElement('style');
            styleSheet.id = 'notification-styles';
            styleSheet.textContent = styles;
            document.head.appendChild(styleSheet);
        }
        
        // Add to document
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    },

    // Event tracking (Google Analytics)
    trackEvent: function(category, action, label = '', value = 0) {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                event_category: category,
                event_label: label,
                value: value
            });
        }
        
        console.log('Event tracked:', { category, action, label, value });
    },

    // Track page views
    trackPageView: function() {
        if (typeof gtag !== 'undefined') {
            gtag('config', 'GA_MEASUREMENT_ID', {
                page_title: document.title,
                page_location: window.location.href
            });
        }
    },

    // Utility functions
    utils: {
        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Format phone number
        formatPhoneNumber: function(phoneNumber) {
            const cleaned = phoneNumber.replace(/\D/g, '');
            const match = cleaned.match(/^(\d{3})(\d{3})(\d{3})(\d{3})$/);
            if (match) {
                return `+${match[1]} ${match[2]} ${match[3]} ${match[4]}`;
            }
            return phoneNumber;
        },

        // Get query parameter
        getQueryParam: function(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    AgriConnect.init();
});

// Handle page visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        AgriConnect.trackEvent('Engagement', 'Page Focused');
    }
});

// Track scroll depth
let maxScrollDepth = 0;
window.addEventListener('scroll', AgriConnect.utils.debounce(function() {
    const scrollDepth = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
    if (scrollDepth > maxScrollDepth) {
        maxScrollDepth = scrollDepth;
        if (maxScrollDepth % 25 === 0) { // Track at 25%, 50%, 75%, 100%
            AgriConnect.trackEvent('Scroll Depth', `${maxScrollDepth}%`);
        }
    }
}, 250));

// Export for global access
window.AgriConnect = AgriConnect;