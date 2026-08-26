/* ===========================================================================
   Rahyaft Sanat — public site behaviour

   Dependency-free, ~4 KB. Every enhancement degrades gracefully: with
   JavaScript disabled the navigation, gallery and filters all still work
   because they are plain links, forms and images in the markup.
   =========================================================================== */
(function () {
    'use strict';

    document.documentElement.classList.remove('no-js');

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* --- Header shadow on scroll ------------------------------------------ */
    var header = document.querySelector('.site-header');
    if (header) {
        var ticking = false;
        var onScroll = function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function () {
                header.classList.toggle('is-scrolled', window.scrollY > 8);
                ticking = false;
            });
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* --- Mobile navigation ------------------------------------------------- */
    var navToggle = document.querySelector('[data-nav-toggle]');
    var nav = document.getElementById('primary-nav');

    if (navToggle && nav) {
        var setNav = function (open) {
            navToggle.setAttribute('aria-expanded', String(open));
            nav.classList.toggle('is-open', open);
            document.body.classList.toggle('nav-open', open);
        };

        navToggle.addEventListener('click', function () {
            setNav(navToggle.getAttribute('aria-expanded') !== 'true');
        });

        // Close when a link is followed or the viewport grows past the breakpoint.
        nav.addEventListener('click', function (event) {
            if (event.target.closest('a')) setNav(false);
        });

        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && navToggle.getAttribute('aria-expanded') === 'true') {
                setNav(false);
                navToggle.focus();
            }
        });

        // Must match the drawer breakpoint in site.css.
        window.addEventListener('resize', function () {
            if (window.innerWidth > 1280) setNav(false);
        });
    }

    /* --- Language switcher -------------------------------------------------- */
    document.querySelectorAll('[data-lang-switch]').forEach(function (root) {
        var toggle = root.querySelector('[data-lang-toggle]');
        if (!toggle) return;

        var close = function () {
            root.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            var open = !root.classList.contains('is-open');
            root.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) close();
        });

        root.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { close(); toggle.focus(); }
        });
    });

    /* --- Product gallery ---------------------------------------------------- */
    var gallery = document.querySelector('[data-gallery]');
    if (gallery) {
        var mainImage = gallery.querySelector('[data-gallery-main]');
        var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-gallery-thumb]'));

        var show = function (thumb) {
            if (!mainImage || !thumb) return;

            var full = thumb.getAttribute('data-full');
            var srcset = thumb.getAttribute('data-srcset');
            var alt = thumb.getAttribute('data-alt') || '';

            if (full) mainImage.src = full;
            mainImage.srcset = srcset || '';
            mainImage.alt = alt;

            thumbs.forEach(function (other) {
                other.setAttribute('aria-current', String(other === thumb));
            });
        };

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () { show(thumb); });
        });

        // Arrow keys move between thumbnails, mirrored for RTL.
        gallery.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;

            var index = thumbs.findIndex(function (t) { return t.getAttribute('aria-current') === 'true'; });
            if (index < 0) return;

            var rtl = document.documentElement.dir === 'rtl';
            var forward = rtl ? event.key === 'ArrowLeft' : event.key === 'ArrowRight';
            var next = thumbs[(index + (forward ? 1 : -1) + thumbs.length) % thumbs.length];

            if (next) { event.preventDefault(); show(next); next.focus(); }
        });
    }

    /* --- Filter form auto-submit -------------------------------------------- */
    document.querySelectorAll('[data-auto-submit]').forEach(function (select) {
        select.addEventListener('change', function () {
            var form = select.closest('form');
            if (form) form.submit();
        });
    });

    /* --- Copy to clipboard --------------------------------------------------- */
    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var value = button.getAttribute('data-copy') || '';
            var done = function () {
                var original = button.getAttribute('data-label-default') || button.textContent;
                button.textContent = button.getAttribute('data-label-copied') || 'Copied';
                window.setTimeout(function () { button.textContent = original; }, 1600);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(done).catch(function () {});
                return;
            }

            // Fallback for non-secure contexts (plain HTTP staging hosts).
            var field = document.createElement('textarea');
            field.value = value;
            field.setAttribute('readonly', '');
            field.style.position = 'absolute';
            field.style.left = '-9999px';
            document.body.appendChild(field);
            field.select();
            try { document.execCommand('copy'); done(); } catch (error) { /* ignore */ }
            document.body.removeChild(field);
        });
    });

    /* --- Scroll reveal -------------------------------------------------------- */
    var revealables = document.querySelectorAll('.reveal');
    if (revealables.length) {
        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealables.forEach(function (el) { el.classList.add('is-visible'); });
        } else {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

            revealables.forEach(function (el) { observer.observe(el); });
        }
    }

    /* --- Contact form submit state -------------------------------------------- */
    document.querySelectorAll('[data-pending-form]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('[type="submit"]');
            if (!button || button.dataset.busy === '1') return;

            // Guard against a double submission creating two enquiries.
            button.dataset.busy = '1';
            button.setAttribute('aria-disabled', 'true');

            var pending = button.getAttribute('data-label-pending');
            if (pending) {
                button.dataset.labelOriginal = button.textContent;
                button.textContent = pending;
            }
        });
    });
}());
