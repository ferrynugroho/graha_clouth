/**
 * GrahaClouth – Main JavaScript
 * Modern Fashion Store UI Interactions
 */

document.addEventListener('DOMContentLoaded', function () {

    // ===== NAVBAR SCROLL EFFECT =====
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        });
    }

    // ===== HAMBURGER MOBILE MENU =====
    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobileNav');
    if (hamburger && mobileNav) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('open');
            mobileNav.classList.toggle('open');
            document.body.style.overflow = mobileNav.classList.contains('open') ? 'hidden' : '';
        });
    }

    // ===== SEARCH OVERLAY =====
    const searchOverlay = document.getElementById('searchOverlay');
    const searchTrigger = document.getElementById('searchTrigger');
    const searchClose   = document.getElementById('searchClose');
    const searchInput   = document.getElementById('searchMainInput');

    if (searchTrigger && searchOverlay) {
        searchTrigger.addEventListener('click', () => {
            searchOverlay.classList.add('open');
            setTimeout(() => searchInput && searchInput.focus(), 150);
        });
    }
    if (searchClose && searchOverlay) {
        searchClose.addEventListener('click', () => searchOverlay.classList.remove('open'));
    }
    if (searchOverlay) {
        searchOverlay.addEventListener('click', (e) => {
            if (e.target === searchOverlay) searchOverlay.classList.remove('open');
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') searchOverlay.classList.remove('open');
        });
    }

    // ===== SCROLL REVEAL ANIMATION =====
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (revealEls.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el    = entry.target;
                    const delay = el.dataset.delay || 0;
                    setTimeout(() => {
                        el.style.opacity  = '1';
                        el.style.transform = 'translateY(0)';
                    }, delay);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.12 });

        revealEls.forEach(el => {
            el.style.opacity   = '0';
            el.style.transform = 'translateY(24px)';
            el.style.transition = 'opacity 0.55s ease, transform 0.55s ease';
            observer.observe(el);
        });
    }

    // ===== ACTIVE NAV LINK =====
    const currentPath = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-menu a, .mobile-nav a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href === currentPath) link.classList.add('active');
        if (!currentPath && href === 'index.php') link.classList.add('active');
    });

    // ===== CART ADD FEEDBACK (toast) =====
    document.querySelectorAll('.btn-quick-add, [data-cart-add]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.tagName === 'A') return; // let PHP handle
            showToast('<i class="fas fa-check-circle"></i> Produk ditambahkan ke keranjang!', 'success');
        });
    });

    // ===== PRODUCT IMAGE ZOOM (detail page) =====
    const mainImg = document.getElementById('mainProductImg');
    if (mainImg) {
        mainImg.addEventListener('mouseenter', () => mainImg.style.transform = 'scale(1.04)');
        mainImg.addEventListener('mouseleave', () => mainImg.style.transform = 'scale(1)');
        mainImg.style.transition = 'transform 0.4s ease';
    }

    // ===== QTY STEPPER =====
    document.querySelectorAll('.qty-stepper').forEach(stepper => {
        const minus = stepper.querySelector('[data-minus]');
        const plus  = stepper.querySelector('[data-plus]');
        const input = stepper.querySelector('input');
        if (!minus || !plus || !input) return;

        minus.addEventListener('click', () => {
            const val = parseInt(input.value) || 1;
            if (val > 1) input.value = val - 1;
        });
        plus.addEventListener('click', () => {
            const val = parseInt(input.value) || 1;
            const max = parseInt(input.max) || 999;
            if (val < max) input.value = val + 1;
        });
    });

    // ===== SMOOTH SCROLL (anchor links) =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ===== AUTO DISMISS ALERTS =====
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
        const delay = parseInt(alert.dataset.autoDismiss) || 4000;
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, max-height 0.4s ease';
            alert.style.opacity = '0';
            alert.style.maxHeight = '0';
            alert.style.overflow = 'hidden';
            alert.style.padding = '0';
            alert.style.margin = '0';
            setTimeout(() => alert.remove(), 450);
        }, delay);
    });

});

// ===== GLOBAL TOAST NOTIFICATION =====
function showToast(message, type = 'success', duration = 3500) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = `
            position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;
            display: flex; flex-direction: column; gap: 0.75rem;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }

    const colors = {
        success: { bg: '#ECFDF5', text: '#065F46', border: '#A7F3D0', icon: '#10B981' },
        danger:  { bg: '#FEF2F2', text: '#991B1B', border: '#FECACA', icon: '#EF4444' },
        warning: { bg: '#FFFBEB', text: '#92400E', border: '#FDE68A', icon: '#F59E0B' },
        info:    { bg: '#EFF6FF', text: '#1E40AF', border: '#BFDBFE', icon: '#3B82F6' },
        accent:  { bg: '#FEF3E8', text: '#92400E', border: '#FDE0C0', icon: '#E8892D' },
    };
    const c = colors[type] || colors.success;

    const toast = document.createElement('div');
    toast.style.cssText = `
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.9rem 1.25rem;
        background: ${c.bg}; color: ${c.text};
        border: 1px solid ${c.border}; border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        font-size: 0.875rem; font-weight: 600;
        font-family: 'Inter', sans-serif;
        min-width: 240px; max-width: 340px;
        pointer-events: all;
        animation: toastSlideIn 0.3s ease both;
    `;
    toast.innerHTML = message;

    const style = document.createElement('style');
    style.textContent = `
        @keyframes toastSlideIn {
            from { opacity: 0; transform: translateX(30px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes toastSlideOut {
            from { opacity: 1; transform: translateX(0); }
            to   { opacity: 0; transform: translateX(30px); }
        }
    `;
    if (!document.getElementById('toastStyles')) {
        style.id = 'toastStyles';
        document.head.appendChild(style);
    }

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'toastSlideOut 0.3s ease both';
        setTimeout(() => toast.remove(), 320);
    }, duration);
}

// ===== FORMAT RUPIAH (helper) =====
function formatRupiah(number) {
    return 'Rp ' + parseInt(number).toLocaleString('id-ID');
}
