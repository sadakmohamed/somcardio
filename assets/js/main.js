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

    // Show the complete member profile without expanding the card grid.
    document.querySelectorAll('.member-profile-button').forEach(button => {
        button.addEventListener('click', () => {
            const member = JSON.parse(button.dataset.member);
            const escapeHtml = value => String(value || '').replace(/[&<>'"]/g, character => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
            }[character]));

            const expHtml = member.experience ? `<span><strong>${escapeHtml(member.experience)}</strong></span>` : '';
            
            // Render rich bio cleanly without displaying raw HTML tags
            let bioContent = member.bio || '';
            // If bioContent has escaped tags like &lt;p&gt;, unescape them
            if (bioContent.includes('&lt;') && bioContent.includes('&gt;')) {
                const txt = document.createElement('textarea');
                txt.innerHTML = bioContent;
                bioContent = txt.value;
            }

            Swal.fire({
                imageUrl: member.image,
                imageAlt: member.name,
                title: escapeHtml(member.name),
                html: `<div class="member-profile-alert">
                    <p class="member-profile-specialization">${escapeHtml(member.specialization)}</p>
                    <div class="member-profile-facts">
                        ${expHtml}
                        <span>${escapeHtml(member.hospital)}</span>
                    </div>
                    <div class="member-profile-bio">
                        <span>Biography</span>
                        <div class="member-profile-bio-content" style="text-align:left;line-height:1.6;margin-top:8px;color:var(--text-secondary);font-size:0.92rem;">${bioContent}</div>
                    </div>
                </div>`,
                confirmButtonText: 'Close',
                confirmButtonColor: '#27AAE1',
                width: 'min(620px, calc(100% - 32px))',
                customClass: { popup: 'member-profile-popup' }
            });
        });
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
            'guidelines': ['guidelines', 'clinical guidelines', 'clinical-guidelines'],
            'research': ['research', 'publication', 'publications', 'research & publications'],
            'education': ['education', 'course', 'seminar', 'webinar', 'workshop', 'education & training'],
            'events': ['events', 'workshop', 'seminar', 'webinar'],
            'news': ['news']
        };

        const applyFilter = (filter, isInitial = false) => {
            const allowedCategories = categoryMap[filter] || (filter === 'all' ? null : [filter]);

            filterItems.forEach(item => {
                const itemCat = (item.dataset.category || '').toLowerCase().trim();
                const isMatch = (filter === 'all') || (allowedCategories && allowedCategories.includes(itemCat));

                if (isMatch) {
                    item.style.display = '';
                    if (!isInitial) {
                        setTimeout(() => { item.style.opacity = '1'; item.style.transform = 'translateY(0)'; }, 30);
                    } else {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }
                } else {
                    if (!isInitial) {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(20px)';
                        setTimeout(() => { item.style.display = 'none'; }, 250);
                    } else {
                        item.style.display = 'none';
                        item.style.opacity = '0';
                    }
                }
            });
        };

        filterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                filterTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const filter = (tab.dataset.filter || 'all').toLowerCase();
                applyFilter(filter, false);

                // Update URL query string without reloading page
                const url = new URL(window.location.href);
                if (filter === 'all') {
                    url.searchParams.delete('category');
                } else {
                    url.searchParams.set('category', filter);
                }
                window.history.replaceState({}, '', url.toString());
            });
        });

        // Auto-filter on page load based on active button or URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const urlCategory = (urlParams.get('category') || '').toLowerCase().trim();
        let targetTab = document.querySelector('.filter-tab.active');

        if (urlCategory) {
            const matchingTab = Array.from(filterTabs).find(t => (t.dataset.filter || '').toLowerCase().trim() === urlCategory);
            if (matchingTab) {
                filterTabs.forEach(t => t.classList.remove('active'));
                matchingTab.classList.add('active');
                targetTab = matchingTab;
            }
        }

        if (targetTab) {
            const initialFilter = (targetTab.dataset.filter || 'all').toLowerCase().trim();
            applyFilter(initialFilter, true);
        }
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
