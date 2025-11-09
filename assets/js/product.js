/**
 * PYRASTORE - Product Detail Page JavaScript
 * Image gallery, lightbox, and interactive features
 */

// ==================== Image Gallery Functionality ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Product page JavaScript loaded');
    initializeImageGallery();
    initializeLightbox();
    initializeShareButtons();
});

function initializeImageGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail-item img');
    const mainImage = document.getElementById('mainImage');

    console.log('Gallery init - Thumbnails found:', thumbnails.length);
    console.log('Gallery init - Main image:', mainImage ? 'Found' : 'Not found');

    if (!thumbnails.length || !mainImage) {
        console.warn('Gallery initialization failed - missing elements');
        return;
    }

    thumbnails.forEach(function(thumbnail, index) {
        console.log('Adding click handler to thumbnail', index + 1);

        // Add click event
        thumbnail.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Thumbnail clicked:', index + 1, 'New src:', this.src);

            // Store current src for comparison
            const oldSrc = mainImage.src;
            const newSrc = this.src;

            // Update main image source
            mainImage.src = newSrc;
            mainImage.alt = this.alt || 'Product Image';

            console.log('Main image updated from:', oldSrc, 'to:', newSrc);

            // Remove active class from all thumbnail containers
            document.querySelectorAll('.thumbnail-item').forEach(function(item) {
                item.classList.remove('active');
            });

            // Add active class to clicked thumbnail's parent
            const parent = this.closest('.thumbnail-item');
            if (parent) {
                parent.classList.add('active');
                console.log('Active class added to thumbnail', index + 1);
            }

            // Add smooth zoom animation
            mainImage.style.transition = 'transform 0.3s ease';
            mainImage.style.transform = 'scale(0.95)';
            setTimeout(function() {
                mainImage.style.transform = 'scale(1)';
            }, 150);
        });

        // Add hover effect for desktop
        thumbnail.addEventListener('mouseenter', function() {
            this.style.transition = 'transform 0.2s ease';
            this.style.transform = 'scale(1.05)';
        });

        thumbnail.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });

        // Make thumbnails more obviously clickable
        thumbnail.style.cursor = 'pointer';
    });

    console.log('Gallery initialized successfully with', thumbnails.length, 'thumbnails');
}

// ==================== Lightbox Functionality ====================
function initializeLightbox() {
    const mainImage = document.getElementById('mainImage');
    const zoomBadge = document.querySelector('.zoom-badge');
    const mainImageContainer = document.querySelector('.main-image-container');

    if (!mainImage) return;

    // Click on zoom badge
    if (zoomBadge) {
        zoomBadge.addEventListener('click', function(e) {
            e.stopPropagation();
            openLightbox(mainImage.src, mainImage.alt);
        });
    }

    // Click on main image
    if (mainImageContainer) {
        mainImageContainer.addEventListener('click', function(e) {
            if (e.target === mainImage) {
                openLightbox(mainImage.src, mainImage.alt);
            }
        });

        // Add cursor pointer to indicate clickable
        mainImage.style.cursor = 'zoom-in';
    }
}

function openLightbox(imageSrc, imageAlt) {
    // Create lightbox overlay
    const lightbox = document.createElement('div');
    lightbox.className = 'image-lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img src="${imageSrc}" alt="${imageAlt || 'Product Image'}" class="lightbox-image">
            <div class="lightbox-controls">
                <button class="lightbox-zoom-in" aria-label="تكبير">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button class="lightbox-zoom-out" aria-label="تصغير">
                    <i class="fas fa-search-minus"></i>
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';

    // Animate in
    setTimeout(function() {
        lightbox.classList.add('active');
    }, 10);

    const lightboxImage = lightbox.querySelector('.lightbox-image');
    let currentZoom = 1;

    // Zoom controls
    lightbox.querySelector('.lightbox-zoom-in').addEventListener('click', function(e) {
        e.stopPropagation();
        currentZoom = Math.min(currentZoom + 0.25, 3);
        lightboxImage.style.transform = `scale(${currentZoom})`;
    });

    lightbox.querySelector('.lightbox-zoom-out').addEventListener('click', function(e) {
        e.stopPropagation();
        currentZoom = Math.max(currentZoom - 0.25, 1);
        lightboxImage.style.transform = `scale(${currentZoom})`;
    });

    // Close lightbox
    function closeLightbox() {
        lightbox.classList.remove('active');
        setTimeout(function() {
            document.body.removeChild(lightbox);
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // Close on click outside or close button
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
            closeLightbox();
        }
    });

    // Close on ESC key
    const escHandler = function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}

// ==================== Share Button Improvements ====================
function initializeShareButtons() {
    // Add copy success animation
    const copyBtn = document.querySelector('.share-btn.copy');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> تم النسخ!';
            this.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';

            setTimeout(function() {
                copyBtn.innerHTML = originalText;
                copyBtn.style.background = '';
            }, 2000);
        });
    }
}

// ==================== Sticky Buy Button on Mobile ====================
window.addEventListener('scroll', function() {
    const buyBtn = document.querySelector('.buy-now-btn');
    const priceSection = document.querySelector('.price-section');

    if (!buyBtn || !priceSection || window.innerWidth > 768) return;

    const priceSectionBottom = priceSection.getBoundingClientRect().bottom;

    if (priceSectionBottom < 0) {
        buyBtn.classList.add('sticky-mobile');
    } else {
        buyBtn.classList.remove('sticky-mobile');
    }
}, { passive: true });

// ==================== Smooth Scroll for Anchor Links ====================
document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ==================== Review Rating Animation ====================
function animateReviewStars() {
    const reviewRatings = document.querySelectorAll('.review-rating');

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                const stars = entry.target.querySelectorAll('.fa-star');
                stars.forEach(function(star, index) {
                    setTimeout(function() {
                        star.style.transform = 'scale(1.2)';
                        setTimeout(function() {
                            star.style.transform = 'scale(1)';
                        }, 150);
                    }, index * 100);
                });
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    reviewRatings.forEach(function(rating) {
        observer.observe(rating);
    });
}

document.addEventListener('DOMContentLoaded', animateReviewStars);

// ==================== Image Loading Error Handler ====================
document.addEventListener('DOMContentLoaded', function() {
    const productImages = document.querySelectorAll('.product-image, .main-image, .thumbnail-item img');

    productImages.forEach(function(img) {
        img.addEventListener('error', function() {
            this.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect fill="%23f0f0f0" width="400" height="400"/%3E%3Ctext fill="%23999" font-family="Arial" font-size="20" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
            this.style.objectFit = 'contain';
        });
    });
});

// ==================== Performance: Lazy Load Similar Products ====================
document.addEventListener('DOMContentLoaded', function() {
    const similarProducts = document.querySelectorAll('.similar-products-grid .product-card');

    if ('IntersectionObserver' in window && similarProducts.length) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        similarProducts.forEach(function(card) {
            observer.observe(card);
        });
    }
});
