/* DPS Parent Portal — small progressive enhancements. No dependencies. */
(function () {
    'use strict';

    // CNIC: 12345-1234567-1
    document.querySelectorAll('[data-cnic]').forEach(function (input) {
        input.addEventListener('input', function () {
            var d = input.value.replace(/\D/g, '').slice(0, 13);
            if (d.length > 12) d = d.slice(0, 5) + '-' + d.slice(5, 12) + '-' + d.slice(12);
            else if (d.length > 5) d = d.slice(0, 5) + '-' + d.slice(5);
            input.value = d;
        });
    });

    // Show / hide password
    document.querySelectorAll('[data-reveal]').forEach(function (btn) {
        var input = document.getElementById(btn.getAttribute('data-reveal'));
        if (!input) return;
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            var icon = btn.querySelector('i');
            if (icon) { icon.classList.toggle('fa-eye', !show); icon.classList.toggle('fa-eye-slash', show); }
        });
    });

    // Copy consumer number
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = btn.getAttribute('data-copy');
            var label = btn.querySelector('span');
            var done = function () {
                btn.classList.add('is-done');
                if (label) label.textContent = 'Copied';
                setTimeout(function () { btn.classList.remove('is-done'); if (label) label.textContent = 'Copy'; }, 2000);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done, function () { fallbackCopy(text); done(); });
            } else {
                fallbackCopy(text); done();
            }
        });
    });
    function fallbackCopy(text) {
        var t = document.createElement('textarea');
        t.value = text; t.setAttribute('readonly', ''); t.style.position = 'absolute'; t.style.left = '-9999px';
        document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); } catch (e) { /* ignore */ }
        document.body.removeChild(t);
    }

    // Dismiss flash messages
    document.querySelectorAll('[data-dismiss-flash]').forEach(function (btn) {
        btn.addEventListener('click', function () { var f = btn.closest('.pp-flash'); if (f) f.remove(); });
    });

    // Prevent double submits
    document.querySelectorAll('form[data-once]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var b = form.querySelector('[type="submit"]');
            if (b) { b.disabled = true; if (b.dataset.busy) b.textContent = b.dataset.busy; }
        });
    });

    // Payment proof: preview and shrink large phone photos before upload (server limit 2 MB)
    var MAX_BYTES = 1.8 * 1024 * 1024, MAX_SIDE = 1600;
    document.querySelectorAll('input[type="file"][data-proof]').forEach(function (input) {
        var preview = document.getElementById(input.getAttribute('data-proof'));
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            showPreview(file, file.size);
            if (!/^image\/(jpeg|png|webp|heic|heif)$/i.test(file.type) && !/\.(jpe?g|png)$/i.test(file.name)) return;
            if (file.size <= MAX_BYTES && /^image\/(jpeg|png)$/i.test(file.type)) return;
            if (typeof DataTransfer === 'undefined') return;
            shrink(file).then(function (blob) {
                if (!blob) return;
                var out = new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer();
                dt.items.add(out);
                input.files = dt.files;
                showPreview(out, out.size, file.size);
            });
        });

        function showPreview(file, size, original) {
            if (!preview) return;
            var img = preview.querySelector('img'), note = preview.querySelector('small');
            if (img) {
                if (img.dataset.url) URL.revokeObjectURL(img.dataset.url);
                img.dataset.url = URL.createObjectURL(file);
                img.src = img.dataset.url;
            }
            if (note) {
                note.textContent = file.name + ', ' + kb(size) + (original ? ' (reduced from ' + kb(original) + ')' : '');
            }
            preview.classList.add('is-shown');
        }
    });

    function shrink(file) {
        return new Promise(function (resolve) {
            var url = URL.createObjectURL(file), img = new Image();
            img.onload = function () {
                var scale = Math.min(1, MAX_SIDE / Math.max(img.width, img.height));
                var c = document.createElement('canvas');
                c.width = Math.round(img.width * scale); c.height = Math.round(img.height * scale);
                var ctx = c.getContext('2d');
                ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
                ctx.drawImage(img, 0, 0, c.width, c.height);
                URL.revokeObjectURL(url);
                c.toBlob(function (b) { resolve(b); }, 'image/jpeg', 0.82);
            };
            img.onerror = function () { URL.revokeObjectURL(url); resolve(null); };
            img.src = url;
        });
    }
    function kb(n) { return n > 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.round(n / 1024) + ' KB'; }
})();
