// Motion system: runs first, so the reveal code below sees a single, consistent set of [data-reveal] elements.
(function () {
    'use strict';

    // 1. Section headings rise in word by word. The full sentence stays available to screen readers via aria-label.
    var headings = '.section-title, .stats-heading11, .cta-main-heading, .page-banner h1, .projects-intro h2, .section-head h2,' +
                   ' .cta-band-body h2, .activity-intro h2, .project-detail-block h2, .vm-card h3, .donate-hero h1';
    document.querySelectorAll(headings).forEach(function (el) {
        if (el.children.length || el.dataset.split) return; // only plain-text headings
        var text = el.textContent.trim();
        if (!text) return;
        el.dataset.split = '1';
        el.setAttribute('aria-label', text);
        el.textContent = '';
        text.split(/\s+/).forEach(function (word, i) {
            var outer = document.createElement('span');
            outer.className = 'sw';
            outer.setAttribute('aria-hidden', 'true');
            var inner = document.createElement('span');
            inner.textContent = word;
            inner.style.setProperty('--wi', String(i));
            outer.appendChild(inner);
            el.appendChild(outer);
            el.appendChild(document.createTextNode(' '));
        });
        el.classList.remove('slide-up');
        el.classList.add('split-words');
    });

    // 2. The original fade-ins become the new reveal (slower, staggered); photos zoom, side images slide in.
    document.querySelectorAll('.slide-up').forEach(function (el) {
        el.classList.remove('slide-up');
        if (!el.hasAttribute('data-reveal')) el.setAttribute('data-reveal', '');
    });
    document.querySelectorAll('.scale-in').forEach(function (el) {
        el.classList.remove('scale-in');
        el.setAttribute('data-reveal', 'right');
    });

    // 3. Cards and text blocks on every page animate in, even where the page itself doesn't ask for it.
    var blocks = '.premium-card, .testimonial-card, .activity-card, .focus-card, .gallery-item, .project-tile, .feature-item,' +
                 ' .contact-item, .trust-list li, .quote-box, .stats-row .stat-item, .section-subtitle, .section-badge, .eyebrow,' +
                 ' .footer-grid > div, .map-card, .donate-card, .summary-card, .bank-alt, .thanks-card, .empty-state,' +
                 ' .activity-help, .donate-aside, .project-share, .aside-trust li, .vm-goals li, .hero-subtitle-static';
    document.querySelectorAll(blocks).forEach(function (el) {
        if (el.hasAttribute('data-reveal') || el.closest('.hero-slider, .modal')) return;
        if (el.parentElement && el.parentElement.closest('[data-reveal]') && !el.matches('.trust-list li, .vm-goals li, .aside-trust li, .contact-item, .stats-row .stat-item')) return;
        el.setAttribute('data-reveal', el.matches('.gallery-item, .focus-card') ? 'zoom' : '');
    });

    // 4. Items in the same list arrive one after another.
    ['.trust-list', '.vm-goals', '.aside-trust', '.stats-row', '.footer-grid'].forEach(function (sel) {
        document.querySelectorAll(sel).forEach(function (list) {
            Array.prototype.forEach.call(list.children, function (child, i) {
                if (child.hasAttribute('data-reveal') && !child.style.getPropertyValue('--d')) child.style.setProperty('--d', String(i));
            });
        });
    });
    var contactItems = document.querySelectorAll('.contact-item');
    Array.prototype.forEach.call(contactItems, function (el, i) { el.style.setProperty('--d', String(i % 5)); });
})();

// Shared front-end behaviour (moved here from the inline scripts of the static site).
(function () {
    'use strict';

    var navbar = document.getElementById('mainNavbar');
    var backToTop = document.getElementById('backToTop');
    var floatingBtn = document.getElementById('floatingDonate');

    // Navbar shadow + floating buttons on scroll
    window.addEventListener('scroll', function () {
        var scrolled = window.scrollY > 100;
        if (navbar) navbar.classList.toggle('scrolled', scrolled);
        if (backToTop) backToTop.classList.toggle('show', scrolled);
        if (floatingBtn) floatingBtn.classList.toggle('show', scrolled);
    }, { passive: true });

    if (backToTop) {
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Smooth scroll for in-page anchors, offset by the sticky navbar
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var href = this.getAttribute('href');
            if (href === '#' || href === '') { e.preventDefault(); return; }
            var target = document.querySelector(href);
            if (!target) return;
            e.preventDefault();
            window.scrollTo({ top: target.offsetTop - (navbar ? navbar.offsetHeight : 0), behavior: 'smooth' });
        });
    });

    // Close the mobile menu when tapping outside it
    document.addEventListener('click', function (event) {
        var collapse = document.querySelector('.navbar-collapse');
        var toggler = document.querySelector('.navbar-toggler');
        if (collapse && collapse.classList.contains('show') && !collapse.contains(event.target) && !toggler.contains(event.target)) {
            var bs = window.bootstrap && bootstrap.Collapse.getInstance(collapse);
            if (bs) bs.hide();
        }
    });

    // Scroll reveal animations
    var revealOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };

    // Cards in the same grid row arrive one after another (unless a delay is already set in the page).
    document.querySelectorAll('.row').forEach(function (row) {
        Array.prototype.forEach.call(row.children, function (col, i) {
            col.querySelectorAll(':scope > [data-reveal], :scope > * > [data-reveal], :scope > .slide-up, :scope > * > .slide-up').forEach(function (el) {
                if (!el.style.getPropertyValue('--d')) el.style.setProperty('--d', String(i % 4));
            });
        });
    });

    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('[data-reveal], .img-reveal, .split-words').forEach(function (el) { el.classList.add('is-visible'); });
        document.querySelectorAll('.slide-up, .scale-in').forEach(function (el) { el.classList.add('active'); });
        document.querySelectorAll('.project-card, .stat-card11, .contact-card, .benefit-item, .cta-main-heading, .cta-inner-box').forEach(function (el) { el.classList.add('reveal'); });
        return;
    }

    var activeObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                activeObserver.unobserve(entry.target);
            }
        });
    }, revealOptions);
    document.querySelectorAll('.slide-up, .scale-in').forEach(function (el) { activeObserver.observe(el); });

    var visibleObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                visibleObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('[data-reveal], .img-reveal, .split-words').forEach(function (el) { visibleObserver.observe(el); });

    var revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) entry.target.classList.add('reveal');
        });
    }, revealOptions);
    document.querySelectorAll('.project-card, .stat-card11, .contact-card, .benefit-item, .cta-main-heading, .cta-inner-box')
        .forEach(function (el) { revealObserver.observe(el); });

    // Counters: <div class="counter" data-target="1000" data-prefix="₹" data-suffix="+">
    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-target'), 10) || 0;
        var prefix = el.getAttribute('data-prefix') || '';
        var suffix = el.getAttribute('data-suffix') || '';
        var start = null;
        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / 2000, 1);
            el.textContent = prefix + Math.floor(progress * target).toLocaleString('en-IN') + (progress === 1 ? suffix : '');
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    var counterObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    document.querySelectorAll('.counter, .stat-number11').forEach(function (el) { counterObserver.observe(el); });
})();

// Gallery lightbox: any .js-lightbox link inside [data-gallery]
(function () {
    'use strict';
    var modalEl = document.getElementById('lightboxModal');
    if (!modalEl) return;
    var img = modalEl.querySelector('.lightbox-img');
    var caption = modalEl.querySelector('.lightbox-caption');
    var items = Array.prototype.slice.call(document.querySelectorAll('.js-lightbox'));
    var index = 0;

    function show(i) {
        index = (i + items.length) % items.length;
        var item = items[index];
        img.src = item.getAttribute('href');
        img.alt = item.getAttribute('data-caption') || '';
        caption.textContent = item.getAttribute('data-caption') || '';
    }

    items.forEach(function (item, i) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            show(i);
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    });
    modalEl.querySelector('.lightbox-prev').addEventListener('click', function () { show(index - 1); });
    modalEl.querySelector('.lightbox-next').addEventListener('click', function () { show(index + 1); });
    document.addEventListener('keydown', function (e) {
        if (!modalEl.classList.contains('show')) return;
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
    });
})();
