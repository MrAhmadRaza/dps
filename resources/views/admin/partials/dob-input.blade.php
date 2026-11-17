{{--
    Fast date-of-birth field: type the date (DD/MM/YYYY, slashes are added for you)
    or pick it from a calendar. The visible box has no name; the hidden input
    carries the value to the server as YYYY-MM-DD, exactly like the old
    <input type="date" name="date_of_birth">.
    Use: @include('admin.partials.dob-input', ['value' => 'Y-m-d or empty', 'required' => true])
--}}
@php
    $dobValue = $value ?? '';
    try { $dobDisplay = $dobValue ? \Carbon\Carbon::parse($dobValue)->format('d/m/Y') : ''; } catch (\Throwable $e) { $dobDisplay = ''; $dobValue = ''; }
@endphp
<div class="dob-field" data-dob>
    <div class="input-group">
        <input type="text" id="date_of_birth" class="form-control" value="{{ $dobDisplay }}" placeholder="DD/MM/YYYY"
               inputmode="numeric" autocomplete="off" maxlength="10" @if (!empty($required)) required @endif
               aria-describedby="dobHint" data-dob-text>
        <button type="button" class="btn btn-light" data-dob-open aria-label="Pick from calendar" title="Pick from calendar">
            <i class="fa-regular fa-calendar"></i>
        </button>
    </div>
    <input type="hidden" name="date_of_birth" value="{{ $dobValue }}" data-dob-value>
    <input type="date" class="dob-native" tabindex="-1" aria-hidden="true" max="{{ now()->toDateString() }}" value="{{ $dobValue }}" data-dob-native>
    <div class="invalid-feedback" data-dob-error>Enter the date as DD/MM/YYYY, e.g. 14/08/2016.</div>
    <div class="form-text" id="dobHint" data-dob-hint>Type it like 14082016, the slashes are added for you.</div>
</div>
@once
@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-dob]').forEach(function (box) {
        var text = box.querySelector('[data-dob-text]'),
            hidden = box.querySelector('[data-dob-value]'),
            nativeIn = box.querySelector('[data-dob-native]'),
            hint = box.querySelector('[data-dob-hint]'),
            error = box.querySelector('[data-dob-error]'),
            defaultHint = hint.textContent;

        function pad(n) { return String(n).padStart(2, '0'); }

        function parse(v) {
            var m = v.trim().match(/^(\d{1,2})[\/\-. ](\d{1,2})[\/\-. ](\d{4})$/);
            if (!m) return null;
            var d = +m[1], mo = +m[2], y = +m[3];
            var dt = new Date(y, mo - 1, d);
            if (dt.getFullYear() !== y || dt.getMonth() !== mo - 1 || dt.getDate() !== d) return null;
            var today = new Date();
            if (dt > today || y < today.getFullYear() - 40) return 'range';
            return { iso: y + '-' + pad(mo) + '-' + pad(d), date: dt };
        }

        function age(dt) {
            var t = new Date(), y = t.getFullYear() - dt.getFullYear(), m = t.getMonth() - dt.getMonth();
            if (t.getDate() < dt.getDate()) m--;
            if (m < 0) { y--; m += 12; }
            return y + (y === 1 ? ' year' : ' years') + (m ? ', ' + m + (m === 1 ? ' month' : ' months') : '');
        }

        function check(showErrors) {
            var v = text.value.trim();
            if (v === '') {
                hidden.value = '';
                text.classList.remove('is-valid');
                hint.textContent = defaultHint;
                return !text.required;
            }
            var r = parse(v);
            if (r && r !== 'range') {
                hidden.value = r.iso;
                nativeIn.value = r.iso;
                text.classList.remove('is-invalid');
                text.classList.add('is-valid');
                hint.textContent = 'Age today: ' + age(r.date);
                return true;
            }
            hidden.value = '';
            text.classList.remove('is-valid');
            hint.textContent = defaultHint;
            if (showErrors) {
                text.classList.add('is-invalid');
                text.classList.remove('is-valid');
                error.textContent = r === 'range'
                    ? 'That date is in the future or too far back. Please check the year.'
                    : 'Enter the date as DD/MM/YYYY, e.g. 14/08/2016.';
            }
            return false;
        }

        // Auto-insert slashes while typing digits. Typing a separator after a
        // single digit pads it (5/3/2019 -> 05/03/2019).
        text.addEventListener('input', function (e) {
            if (e.inputType && e.inputType.indexOf('delete') === 0) { check(false); return; }
            var raw = text.value;
            if (e.data && /^[\/\-. ]$/.test(e.data)) {
                var parts = raw.slice(0, -1).split(/[\/\-. ]/).filter(function (x) { return x !== ''; });
                if (parts.length && parts.length < 3 && parts[parts.length - 1].length === 1) parts[parts.length - 1] = '0' + parts[parts.length - 1];
                raw = parts.join('/') + (parts.length && parts.length < 3 ? '/' : '');
            }
            var digits = raw.replace(/\D/g, '').slice(0, 8);
            var out = digits.slice(0, 2);
            if (digits.length > 2) out += '/' + digits.slice(2, 4);
            if (digits.length > 4) out += '/' + digits.slice(4);
            if ((digits.length === 2 || digits.length === 4) && !(e.inputType || '').startsWith('insertFromPaste')) out += '/';
            text.value = out;
            text.classList.remove('is-invalid');
            check(false);
        });
        text.addEventListener('blur', function () { check(true); });

        // Calendar button opens the browser's picker as a fallback
        box.querySelector('[data-dob-open]').addEventListener('click', function () {
            try { nativeIn.showPicker(); } catch (e) { nativeIn.focus(); nativeIn.click(); }
        });
        nativeIn.addEventListener('change', function () {
            if (!nativeIn.value) return;
            var p = nativeIn.value.split('-');
            text.value = p[2] + '/' + p[1] + '/' + p[0];
            check(true);
        });

        // Block "Next" / submit while the date is not valid
        var form = text.form;
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.next-step') || !box.closest('.tab-pane.active')) return;
            if (!check(true)) { e.stopImmediatePropagation(); e.preventDefault(); text.focus(); }
        }, true);
        if (form) form.addEventListener('submit', function (e) {
            if (!check(true)) { e.preventDefault(); e.stopImmediatePropagation(); }
        }, true);

        check(false);
    });
})();
</script>
@endpush
@endonce
