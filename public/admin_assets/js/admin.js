/* DPS Staff Portal: small helpers. jQuery and Bootstrap are loaded before this file. */
(function () {
    'use strict';

    var body = document.body;

    /* Sidebar drawer on phones/tablets */
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-nav-toggle]')) {
            body.classList.toggle('nav-open');
        } else if (e.target.closest('.ad-backdrop')) {
            body.classList.remove('nav-open');
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') body.classList.remove('nav-open');
    });

    /* Flash messages: success/info fade out, errors stay until closed */
    function dismiss(el) {
        el.classList.add('is-leaving');
        setTimeout(function () { el.remove(); }, 220);
    }
    document.querySelectorAll('.flash').forEach(function (el) {
        var close = el.querySelector('.btn-close');
        if (close) close.addEventListener('click', function () { dismiss(el); });
        if (el.dataset.autohide === '1') {
            var timer = setTimeout(function () { dismiss(el); }, 6000);
            el.addEventListener('mouseenter', function () { clearTimeout(timer); });
        }
    });

    /* Confirm step for destructive / state-changing forms:
       <form data-confirm="Delete this challan?" data-confirm-ok="Delete" data-confirm-tone="danger"> */
    var modalEl = document.getElementById('confirmModal');
    var pending = null;
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-confirm]') || form.dataset.confirmed === '1') return;
        if (!modalEl || typeof bootstrap === 'undefined') {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
            return;
        }
        e.preventDefault();
        pending = form;
        modalEl.querySelector('[data-confirm-text]').textContent = form.dataset.confirm;
        modalEl.querySelector('[data-confirm-title]').textContent = form.dataset.confirmTitle || 'Please confirm';
        var ok = modalEl.querySelector('[data-confirm-accept]');
        ok.textContent = form.dataset.confirmOk || 'Confirm';
        ok.className = 'btn ' + (form.dataset.confirmTone === 'danger' ? 'btn-danger' : 'btn-primary');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }, true);
    if (modalEl) {
        modalEl.querySelector('[data-confirm-accept]').addEventListener('click', function () {
            if (!pending) return;
            pending.dataset.confirmed = '1';
            var btn = this;
            btn.disabled = true;
            pending.submit();
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            modalEl.querySelector('[data-confirm-accept]').disabled = false;
            pending = null;
        });
    }

    /* Copy to clipboard: <button class="copy-btn" data-copy="10045100001"> */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-copy]');
        if (!btn) return;
        var text = btn.dataset.copy;
        var done = function () {
            btn.classList.add('is-done');
            var icon = btn.querySelector('i');
            if (icon) { icon.className = 'fa-solid fa-check'; }
            setTimeout(function () {
                btn.classList.remove('is-done');
                if (icon) icon.className = 'fa-regular fa-copy';
            }, 1500);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done);
        } else {
            var t = document.createElement('textarea');
            t.value = text; document.body.appendChild(t); t.select();
            try { document.execCommand('copy'); done(); } catch (err) { /* ignore */ }
            t.remove();
        }
    });

    /* Image preview modal: <img data-zoom src="..."> */
    document.addEventListener('click', function (e) {
        var img = e.target.closest('[data-zoom]');
        var m = document.getElementById('zoomModal');
        if (!img || !m || typeof bootstrap === 'undefined') return;
        e.preventDefault();
        m.querySelector('img').src = img.dataset.zoom || img.src;
        bootstrap.Modal.getOrCreateInstance(m).show();
    });

    /* Shared DataTables defaults */
    if (window.jQuery && jQuery.fn.dataTable) {
        jQuery.extend(true, jQuery.fn.dataTable.defaults, {
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            language: {
                search: '',
                searchPlaceholder: 'Search…',
                lengthMenu: 'Show _MENU_',
                info: '_START_–_END_ of _TOTAL_',
                infoEmpty: 'No records',
                infoFiltered: '(filtered from _MAX_)',
                zeroRecords: 'Nothing matches your search',
                processing: '<span class="spinner-border spinner-border-sm me-2"></span>Loading…',
                paginate: { previous: '<i class="fa-solid fa-chevron-left"></i>', next: '<i class="fa-solid fa-chevron-right"></i>' }
            }
        });
        jQuery.fn.dataTable.ext.errMode = 'none';
        jQuery(document).on('error.dt', function (e, settings, techNote, message) {
            if (window.console) console.warn('DataTables:', message);
        });
        /* Keep scrollX header aligned when the window or the sidebar changes size */
        var t;
        window.addEventListener('resize', function () {
            clearTimeout(t);
            t = setTimeout(function () { jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust(); }, 150);
        });
    }
})();
