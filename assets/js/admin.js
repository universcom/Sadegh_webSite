/* ===========================================================================
   Rahyaft Sanat — administration panel behaviour.
   Dependency-free. Every control degrades to a working form control without JS.
   =========================================================================== */
(function () {
    'use strict';

    /* --- Sidebar (mobile) --------------------------------------------------- */
    var sidebar = document.querySelector('.sidebar');
    var toggle = document.querySelector('[data-menu-toggle]');
    var scrim = document.querySelector('.scrim');

    if (sidebar && toggle) {
        var setMenu = function (open) {
            sidebar.classList.toggle('is-open', open);
            if (scrim) scrim.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
        };

        toggle.addEventListener('click', function () {
            setMenu(toggle.getAttribute('aria-expanded') !== 'true');
        });
        if (scrim) scrim.addEventListener('click', function () { setMenu(false); });
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setMenu(false);
        });
    }

    /* --- Language tabs ------------------------------------------------------- */
    document.querySelectorAll('[data-lang-tabs]').forEach(function (group) {
        var tabs = Array.prototype.slice.call(group.querySelectorAll('[role="tab"]'));

        var select = function (tab) {
            tabs.forEach(function (other) {
                var selected = other === tab;
                other.setAttribute('aria-selected', String(selected));
                other.tabIndex = selected ? 0 : -1;

                var panel = document.getElementById(other.getAttribute('aria-controls'));
                if (panel) panel.hidden = !selected;
            });
        };

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () { select(tab); });
            tab.addEventListener('keydown', function (e) {
                if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
                var i = tabs.indexOf(tab);
                var next = tabs[(i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
                e.preventDefault();
                select(next);
                next.focus();
            });
        });
    });

    /* --- Destructive action confirmation ------------------------------------- */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        var message = form.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
            e.preventDefault();
        }
    });

    /* --- Auto-submit filter controls ----------------------------------------- */
    document.querySelectorAll('[data-auto-submit]').forEach(function (control) {
        control.addEventListener('change', function () {
            var form = control.closest('form');
            if (form) form.submit();
        });
    });

    /* --- Slug helper ---------------------------------------------------------- */
    // Mirrors the server-side slugify(): non-Latin titles produce nothing here,
    // so the field is simply left for the operator to fill in.
    document.querySelectorAll('[data-slug-from]').forEach(function (slugInput) {
        var source = document.getElementById(slugInput.getAttribute('data-slug-from'));
        if (!source) return;

        var touched = slugInput.value.trim() !== '';
        slugInput.addEventListener('input', function () { touched = true; });

        source.addEventListener('input', function () {
            if (touched) return;

            var value = source.value
                .normalize('NFKD')
                .replace(/[̀-ͯ]/g, '')
                .replace(/[^A-Za-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .toLowerCase();

            if (value) slugInput.value = value;
        });
    });

    /* --- Repeatable rows (specs, features, downloads) -------------------------- */
    document.querySelectorAll('[data-repeat]').forEach(function (container) {
        var templateId = container.getAttribute('data-repeat-template');
        var template = document.getElementById(templateId);
        var addButton = document.querySelector('[data-repeat-add="' + container.id + '"]');

        var nextIndex = container.querySelectorAll('[data-repeat-row]').length;

        if (template && addButton) {
            addButton.addEventListener('click', function () {
                var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex++));
                var holder = document.createElement('div');
                holder.innerHTML = html.trim();

                var node = holder.firstElementChild;
                if (node) {
                    container.appendChild(node);
                    var firstField = node.querySelector('input,textarea,select');
                    if (firstField) firstField.focus();
                }
            });
        }

        container.addEventListener('click', function (e) {
            var remove = e.target.closest('[data-repeat-remove]');
            if (!remove) return;

            var row = remove.closest('[data-repeat-row]');
            if (row) row.remove();
        });
    });

    /* --- Copy to clipboard ------------------------------------------------------ */
    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var value = button.getAttribute('data-copy') || '';
            var original = button.getAttribute('title') || '';

            var done = function () {
                button.setAttribute('title', 'Copied');
                window.setTimeout(function () { button.setAttribute('title', original); }, 1500);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(done).catch(function () {});
                return;
            }

            var field = document.createElement('textarea');
            field.value = value;
            field.style.position = 'absolute';
            field.style.left = '-9999px';
            document.body.appendChild(field);
            field.select();
            try { document.execCommand('copy'); done(); } catch (err) { /* ignore */ }
            document.body.removeChild(field);
        });
    });

    /* --- Guard against double submission ------------------------------------------ */
    document.querySelectorAll('form[data-once]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('[type="submit"]');
            if (button) {
                window.setTimeout(function () { button.disabled = true; }, 0);
            }
        });
    });
}());
