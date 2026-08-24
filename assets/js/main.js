/**
 * Somali Cardiac Society — Public JavaScript
 * Brand: #27AAE1 · #ED1C24
 */
document.addEventListener('DOMContentLoaded', () => {

    // ========== Hero entrance stagger ==========
    document.querySelectorAll('.hero-content > *').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        setTimeout(() => {
            el.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 120 + i * 120);
    });

    // ========== Mobile Nav Toggle & Backdrop Click ==========
    const toggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    const navbarEl = document.querySelector('.navbar');

    if (toggle && navLinks) {
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            toggle.classList.toggle('open');
        });
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                toggle.classList.remove('open');
            });
        });
        // Close menu when clicking outside (empty screen / backdrop)
        document.addEventListener('click', (e) => {
            if (navLinks.classList.contains('active')) {
                if (navbarEl && !navbarEl.contains(e.target)) {
                    navLinks.classList.remove('active');
                    toggle.classList.remove('open');
                }
            }
        });
    }

    // Keep desktop dropdowns open briefly while the pointer moves to a submenu.
    document.querySelectorAll('.nav-dual').forEach(dropdown => {
        let closeTimer;
        const openDropdown = () => {
            clearTimeout(closeTimer);
            dropdown.classList.add('is-open');
        };
        const closeDropdown = () => {
            clearTimeout(closeTimer);
            closeTimer = setTimeout(() => dropdown.classList.remove('is-open'), 900);
        };
        dropdown.addEventListener('mouseenter', openDropdown);
        dropdown.addEventListener('mouseleave', closeDropdown);
        dropdown.addEventListener('focusin', openDropdown);
        dropdown.addEventListener('focusout', closeDropdown);
    });

    // ========== Navbar Scroll Effect ==========
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // ========== Fade-in on Scroll (IntersectionObserver) ==========
    const fadeEls = document.querySelectorAll('.fade-in');
    if (fadeEls.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
        fadeEls.forEach(el => observer.observe(el));
    }

    // ========== Back to Top ==========
    const backBtn = document.querySelector('.back-to-top');
    if (backBtn) {
        window.addEventListener('scroll', () => {
            backBtn.classList.toggle('visible', window.scrollY > 400);
        });
        backBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ========== Stat Counter Animation ==========
    const counters = document.querySelectorAll('.stat-number[data-count]');
    if (counters.length) {
        const countObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.dataset.count);
                    const suffix = el.dataset.suffix || '';
                    let current = 0;
                    const step = Math.max(1, Math.floor(target / 60));
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) { current = target; clearInterval(timer); }
                        el.textContent = current + suffix;
                    }, 25);
                    countObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(el => countObserver.observe(el));
    }

    // ========== Filter Tabs ==========
    const filterTabs = document.querySelectorAll('.filter-tab');
    const filterItems = document.querySelectorAll('[data-category]');
    if (filterTabs.length && filterItems.length) {
        // Group sub-categories under main filter tabs
        const categoryMap = {
            'all': null,
            'guidelines': ['guidelines', 'clinical guidelines'],
            'research': ['research', 'publication'],
            'education': ['education', 'course', 'seminar', 'webinar', 'workshop'],
            'events': ['events', 'workshop', 'seminar', 'webinar'],
            'news': ['news']
        };

        filterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                filterTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const filter = tab.dataset.filter.toLowerCase();
                const allowedCategories = categoryMap[filter] || [filter];

                filterItems.forEach(item => {
                    const itemCat = (item.dataset.category || '').toLowerCase();
                    const isMatch = (filter === 'all') || (allowedCategories && allowedCategories.includes(itemCat));

                    if (isMatch) {
                        item.style.display = '';
                        setTimeout(() => { item.style.opacity = '1'; item.style.transform = 'translateY(0)'; }, 50);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(20px)';
                        setTimeout(() => { item.style.display = 'none'; }, 300);
                    }
                });
            });
        });
    }

    // Contact form validation is now handled via SweetAlert AJAX in contact.php

    // ========== Active Nav Link ==========
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-links a').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'index.php')) {
            link.classList.add('active');
        }
    });
});
