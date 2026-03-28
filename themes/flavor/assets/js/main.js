/**
 * GlassPress Flavor Theme - Frontend JavaScript
 */
(function() {
    'use strict';

    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        }, 5000);
    });

    // Lazy load images with native support
    if ('loading' in HTMLImageElement.prototype) {
        document.querySelectorAll('img:not([loading])').forEach(function(img) {
            if (!img.closest('.site-header') && !img.closest('.post-hero')) {
                img.loading = 'lazy';
            }
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // External links open in new tab
    document.querySelectorAll('.prose a').forEach(function(link) {
        if (link.hostname !== window.location.hostname) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });

    // Header scroll effect
    var header = document.querySelector('.site-header');
    if (header) {
        var lastScroll = 0;
        window.addEventListener('scroll', function() {
            var currentScroll = window.pageYOffset;
            if (currentScroll > 100) {
                header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.3)';
            } else {
                header.style.boxShadow = 'none';
            }
            lastScroll = currentScroll;
        }, { passive: true });
    }
})();
