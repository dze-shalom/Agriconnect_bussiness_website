/**
 * AgriConnect Analytics & Tracking
 */

class AgriConnectAnalytics {
    constructor() {
        gtag('config', 'YOUR_ACTUAL_GOOGLE_ANALYTICS_ID'); // Replace with actual ID
        this.debugMode = window.location.hostname === 'localhost';
        this.initialize();
    }

    initialize() {
        // Initialize Google Analytics
        this.initGoogleAnalytics();
        
        // Track page events
        this.trackPageEvents();
        
        // Track user engagement
        this.trackEngagement();
        
        // Track form interactions
        this.trackFormInteractions();
    }

    initGoogleAnalytics() {
        if (this.debugMode) {
            console.log('Analytics initialized in debug mode');
            return;
        }

        // Google Analytics 4
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', this.trackingId, {
            page_title: document.title,
            page_location: window.location.href,
            anonymize_ip: true,
            allow_google_signals: false
        });

        // Enhanced ecommerce for pilot program tracking
        gtag('config', this.trackingId, {
            custom_map: {
                'custom_parameter_1': 'pilot_interest',
                'custom_parameter_2': 'contact_type'
            }
        });
    }

    trackEvent(action, category = 'General', label = '', value = 0) {
        if (this.debugMode) {
            console.log('Event tracked:', { action, category, label, value });
            return;
        }

        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                event_category: category,
                event_label: label,
                value: value,
                custom_parameter_1: label
            });
        }
    }

    trackPageEvents() {
        // Track page scroll depth
        let maxScrollDepth = 0;
        window.addEventListener('scroll', this.debounce(() => {
            const scrollDepth = Math.round(
                (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
            );
            
            if (scrollDepth > maxScrollDepth) {
                maxScrollDepth = scrollDepth;
                
                // Track milestone scroll depths
                if ([25, 50, 75, 90].includes(scrollDepth)) {
                    this.trackEvent('scroll_depth', 'Engagement', `${scrollDepth}%`);
                }
            }
        }, 250));

        // Track time on page
        const startTime = Date.now();
        window.addEventListener('beforeunload', () => {
            const timeSpent = Math.round((Date.now() - startTime) / 1000);
            this.trackEvent('time_on_page', 'Engagement', document.title, timeSpent);
        });

        // Track external link clicks
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.hostname !== window.location.hostname) {
                this.trackEvent('external_link_click', 'Outbound', link.href);
            }
        });

        // Track file downloads
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href.match(/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|zip|rar)$/i)) {
                this.trackEvent('file_download', 'Download', link.href);
            }
        });
    }

    trackEngagement() {
        // Track button clicks
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn, button')) {
                const buttonText = e.target.textContent.trim();
                const buttonClass = e.target.className;
                
                this.trackEvent('button_click', 'Engagement', buttonText);
                
                // Special tracking for important CTAs
                if (buttonClass.includes('btn-primary')) {
                    this.trackEvent('primary_cta_click', 'Conversion', buttonText);
                }
            }
        });

        // Track video interactions (if videos added later)
        document.addEventListener('play', (e) => {
            if (e.target.tagName === 'VIDEO') {
                this.trackEvent('video_play', 'Media', e.target.src);
            }
        }, true);

        // Track form field focus (engagement indicator)
        document.addEventListener('focus', (e) => {
            if (e.target.matches('input, textarea, select')) {
                const formName = e.target.closest('form')?.id || 'unknown_form';
                this.trackEvent('form_field_focus', 'Form Engagement', formName);
            }
        }, true);
    }

    trackFormInteractions() {
        // Track form submissions
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const formName = form.id || form.name || 'unknown_form';
            
            this.trackEvent('form_submission', 'Form', formName);
            
            // Special tracking for pilot program
            if (formName.includes('pilot')) {
                this.trackEvent('pilot_application_submit', 'Conversion', 'Pilot Program');
            }
            
            // Track contact form
            if (formName.includes('contact')) {
                this.trackEvent('contact_form_submit', 'Conversion', 'Contact');
            }
            
            // Track newsletter signup
            if (formName.includes('newsletter')) {
                this.trackEvent('newsletter_signup', 'Conversion', 'Newsletter');
            }
        });

        // Track form abandonment
        const formFields = document.querySelectorAll('input, textarea, select');
        formFields.forEach(field => {
            let fieldTouched = false;
            
            field.addEventListener('focus', () => {
                fieldTouched = true;
            });
            
            field.addEventListener('blur', () => {
                if (fieldTouched && !field.value) {
                    const formName = field.closest('form')?.id || 'unknown_form';
                    this.trackEvent('form_field_abandon', 'Form Abandonment', formName);
                }
            });
        });
    }

    // Track custom business events
    trackPilotInterest(source = 'unknown') {
        this.trackEvent('pilot_interest', 'Business', source);
    }

    trackPartnershipInquiry(type = 'general') {
        this.trackEvent('partnership_inquiry', 'Business', type);
    }

    trackTechnologyPageView(section = 'overview') {
        this.trackEvent('technology_section_view', 'Technology', section);
    }

    trackProgressUpdate(milestone = 'unknown') {
        this.trackEvent('progress_milestone_view', 'Development', milestone);
    }

    // Utility functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // User identification (for logged-in users in future)
    identifyUser(userId, traits = {}) {
        if (this.debugMode) {
            console.log('User identified:', { userId, traits });
            return;
        }

        if (typeof gtag !== 'undefined') {
            gtag('config', this.trackingId, {
                user_id: userId,
                custom_map: traits
            });
        }
    }

    // Track page performance
    trackPerformance() {
        window.addEventListener('load', () => {
            // Track page load time
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            this.trackEvent('page_load_time', 'Performance', document.title, Math.round(loadTime));

            // Track largest contentful paint
            if ('web-vitals' in window) {
                // If Core Web Vitals library is loaded
                getCLS(this.trackWebVital.bind(this));
                getFID(this.trackWebVital.bind(this));
                getFCP(this.trackWebVital.bind(this));
                getLCP(this.trackWebVital.bind(this));
                getTTFB(this.trackWebVital.bind(this));
            }
        });
    }

    trackWebVital(metric) {
        this.trackEvent('web_vital', 'Performance', metric.name, Math.round(metric.value));
    }
}

// Initialize analytics
window.AgriConnectAnalytics = new AgriConnectAnalytics();

// Export for global access
window.trackEvent = (action, category, label, value) => {
    window.AgriConnectAnalytics.trackEvent(action, category, label, value);
};

// Auto-track page view
document.addEventListener('DOMContentLoaded', () => {
    window.AgriConnectAnalytics.trackEvent('page_view', 'Navigation', document.title);
});