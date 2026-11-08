/* Dunyapur Public School — public site behaviour (no dependencies besides Bootstrap/AOS) */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Scroll reveal ---------- */
    if (window.AOS) {
        AOS.init({ duration: 700, once: true, offset: 60, easing: 'ease-out-cubic', disable: reduceMotion });
    }

    /* ---------- Masthead: solid on scroll / when mobile menu open ---------- */
    var masthead = document.getElementById('dpsMasthead');
    var toTop = document.getElementById('dpsToTop');
    function onScroll() {
        var y = window.scrollY || window.pageYOffset;
        if (masthead) masthead.classList.toggle('is-scrolled', y > 40);
        if (toTop) toTop.classList.toggle('is-visible', y > 700);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var nav = document.getElementById('dpsNav');
    if (nav && masthead) {
        nav.addEventListener('show.bs.collapse', function () { masthead.classList.add('is-open'); });
        nav.addEventListener('hidden.bs.collapse', function () { masthead.classList.remove('is-open'); });
        // Close the mobile menu after choosing a section
        nav.querySelectorAll('a[href^="#"]').forEach(function (a) {
            a.addEventListener('click', function () {
                if (nav.classList.contains('show') && window.bootstrap) {
                    bootstrap.Collapse.getOrCreateInstance(nav).hide();
                }
            });
        });
    }

    /* ---------- Active nav link while scrolling ---------- */
    var links = document.querySelectorAll('.dps-nav .nav-link[href^="#"]');
    if ('IntersectionObserver' in window && links.length) {
        var map = {};
        links.forEach(function (l) { map[l.getAttribute('href').slice(1)] = l; });
        var spy = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting && map[e.target.id]) {
                    links.forEach(function (l) { l.classList.remove('active'); });
                    map[e.target.id].classList.add('active');
                }
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        Object.keys(map).forEach(function (id) {
            var el = document.getElementById(id);
            if (el) spy.observe(el);
        });
    }

    /* ---------- Hero slideshow (cross-fade + slow zoom) ---------- */
    var slides = document.querySelectorAll('.dps-hero-slide');
    var dots = document.querySelectorAll('.dps-hero-dots button');
    if (slides.length > 1) {
        // Load the other slides only after the page is ready
        window.addEventListener('load', function () {
            slides.forEach(function (s) {
                if (s.dataset.bg) { s.style.backgroundImage = "url('" + s.dataset.bg + "')"; }
            });
        });
        var current = 0, timer;
        var show = function (i) {
            slides[current].classList.remove('is-active');
            if (dots[current]) dots[current].classList.remove('is-active');
            current = (i + slides.length) % slides.length;
            slides[current].classList.add('is-active');
            if (dots[current]) dots[current].classList.add('is-active');
        };
        var start = function () { if (!reduceMotion) timer = setInterval(function () { show(current + 1); }, 6500); };
        dots.forEach(function (d, i) {
            d.addEventListener('click', function () { clearInterval(timer); show(i); start(); });
        });
        start();
    }

    /* ---------- Count-up numbers ---------- */
    var counters = document.querySelectorAll('[data-count]');
    if ('IntersectionObserver' in window && counters.length && !reduceMotion) {
        var fmt = function (n) { return n.toLocaleString('en-US'); };
        var countObs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                var el = e.target, target = parseInt(el.dataset.count, 10) || 0, t0 = null, dur = 1600;
                var step = function (ts) {
                    if (!t0) t0 = ts;
                    var p = Math.min((ts - t0) / dur, 1), eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = fmt(Math.round(target * eased));
                    if (p < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
                countObs.unobserve(el);
            });
        }, { threshold: .3 });
        counters.forEach(function (c) { countObs.observe(c); });
    }

    /* ---------- Gallery lightbox ---------- */
    var items = Array.prototype.slice.call(document.querySelectorAll('.dps-gallery-item'));
    var lb = document.getElementById('dpsLightbox');
    if (items.length && lb) {
        var img = lb.querySelector('img'), cap = lb.querySelector('figcaption'), idx = 0, lastFocus = null;
        var render = function () {
            img.src = items[idx].dataset.full;
            img.alt = items[idx].dataset.caption || '';
            cap.textContent = items[idx].dataset.caption || '';
        };
        var open = function (i) {
            idx = i; lastFocus = document.activeElement; render();
            lb.hidden = false; document.body.style.overflow = 'hidden';
            lb.querySelector('.dps-lb-close').focus();
        };
        var close = function () {
            lb.hidden = true; document.body.style.overflow = '';
            if (lastFocus) lastFocus.focus();
        };
        var move = function (d) { idx = (idx + d + items.length) % items.length; render(); };

        items.forEach(function (it, i) { it.addEventListener('click', function () { open(i); }); });
        lb.querySelector('.dps-lb-close').addEventListener('click', close);
        lb.querySelector('.dps-lb-prev').addEventListener('click', function () { move(-1); });
        lb.querySelector('.dps-lb-next').addEventListener('click', function () { move(1); });
        lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
        document.addEventListener('keydown', function (e) {
            if (lb.hidden) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') move(-1);
            if (e.key === 'ArrowRight') move(1);
        });
    }
})();
