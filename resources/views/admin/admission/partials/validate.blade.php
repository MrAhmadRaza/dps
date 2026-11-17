{{--
    Client-side check for the admission wizard (new and edit), run on "Next" and
    before the form is sent, so mistakes are shown without a round trip.
    It mirrors the server rules in AdmissionController; the server still validates.
    Needs window.admissionWizard (set by the page's wizard script).
--}}
<script>
(function () {
    var form = document.getElementById('admissionForm');
    if (!form) return;

    var PATTERNS = {
        father_nic:          [/^\d{5}-\d{7}-\d$/, 'Enter the CNIC as 12345-1234567-1.'],
        guardian_nic:        [/^\d{5}-\d{7}-\d$/, 'Enter the CNIC as 12345-1234567-1.'],
        b_form_no:           [/^\d{5}-\d{7}-\d$/, 'Enter the B-Form number as 12345-1234567-1.'],
        contact_no:          [/^03\d{2}-\d{7}$/,  'Enter a mobile number like 0300-1234567.'],
        guardian_contact_no: [/^03\d{2}-\d{7}$/,  'Enter a mobile number like 0300-1234567.']
    };

    // Put an error box at the top of the form
    var summary = document.createElement('div');
    summary.className = 'alert alert-danger d-none';
    summary.setAttribute('role', 'alert');
    summary.setAttribute('tabindex', '-1');
    form.insertBefore(summary, form.firstElementChild.nextSibling);

    function steps() { return Array.prototype.slice.call(form.querySelectorAll('.tab-pane')); }

    function labelFor(field) {
        var col = field.closest('[class*="col-"]') || field.parentElement;
        var label = (field.id && form.querySelector('label[for="' + field.id + '"]')) || (col && col.querySelector('label'));
        var text = label ? label.textContent : (field.name || 'This field');
        return text.replace(/\*/g, '').replace(/\(optional\)/i, '').replace(/\s+/g, ' ').trim();
    }

    // Skip fields inside a hidden block (e.g. the discount fields while Discount is off).
    // Fields in other wizard steps are hidden by the step, not by a block, so they still count.
    function isActive(field) {
        if (field.disabled) return false;
        var el = field.type === 'hidden' ? field.parentElement : field;
        while (el && el !== form) {
            if (!el.classList.contains('tab-pane') && getComputedStyle(el).display === 'none') return false;
            el = el.parentElement;
        }
        return true;
    }

    function feedbackFor(field) {
        var host = field.closest('.input-group') || field;
        var fb = field.parentElement.querySelector(':scope > .invalid-feedback');
        if (!fb && host !== field) fb = host.parentElement.querySelector(':scope > .invalid-feedback');
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            fb.dataset.auto = '1';
            host.insertAdjacentElement('afterend', fb);
        }
        return fb;
    }

    function setError(field, message) {
        var target = field.matches('[name="date_of_birth"]') ? form.querySelector('[data-dob-text]') : field;
        target.classList.add('is-invalid');
        target.classList.remove('is-valid');
        if (target !== field) return; // the date of birth box shows its own message
        var fb = feedbackFor(field);
        if (fb.dataset.auto || !fb.textContent.trim()) fb.textContent = message;
        fb.classList.add('d-block');
    }

    function clearError(field) {
        field.classList.remove('is-invalid');
        var fb = field.parentElement && field.parentElement.querySelector(':scope > .invalid-feedback');
        if (!fb) { var g = field.closest('.input-group'); fb = g && g.parentElement.querySelector(':scope > .invalid-feedback'); }
        if (fb) fb.classList.remove('d-block');
    }

    function checkField(field) {
        if (!field.name || !isActive(field)) return null;
        var value = (field.value || '').trim();
        var label = labelFor(field);

        if (field.name === 'student_photo') {
            return (field.required && !value) ? 'Please capture the student\'s photo.' : null;
        }
        if (field.name === 'date_of_birth') {
            var box = form.querySelector('[data-dob-text]');
            if (!value) return box && box.value.trim() ? 'Enter a real date of birth as DD/MM/YYYY.' : 'Please enter the date of birth.';
            return null;
        }
        if (field.type === 'hidden' || field.type === 'checkbox' || field.type === 'radio') return null;

        if (field.required && value === '') {
            if (field.tagName === 'SELECT') {
                var opts = Array.prototype.map.call(field.options, function (o) { return o.text.trim(); }).filter(Boolean);
                if (opts.indexOf('Yes') !== -1 && opts.indexOf('No') !== -1) return 'Please choose Yes or No.';
                return 'Please choose the ' + label.replace(/\?$/, '').toLowerCase() + '.';
            }
            return 'Please enter the ' + label.replace(/\?$/, '').toLowerCase() + '.';
        }
        if (value === '') return null;
        if (PATTERNS[field.name] && !PATTERNS[field.name][0].test(value)) return PATTERNS[field.name][1];
        if (field.type === 'number' && field.min !== '' && parseFloat(value) < parseFloat(field.min)) return label + ' cannot be below ' + field.min + '.';
        if (field.type === 'number' && isNaN(parseFloat(value))) return label + ' must be a number.';
        return null;
    }

    /** Check one step (or all when stepIndex is null). Returns the list of problems. */
    function validate(stepIndex) {
        var all = steps(), problems = [];
        all.forEach(function (pane, i) {
            if (stepIndex !== null && i !== stepIndex) return;
            pane.querySelectorAll('input, select, textarea').forEach(function (field) {
                var msg = checkField(field);
                if (msg) {
                    setError(field, msg);
                    problems.push({ field: field, step: i, label: labelFor(field), message: msg });
                } else if (field.name) {
                    clearError(field);
                }
            });
        });
        return problems;
    }

    function stepName(i) {
        var s = document.querySelectorAll('.step-progress .step .label')[i];
        return s ? s.textContent.trim() : 'Step ' + (i + 1);
    }

    function focusFirst(problem) {
        var target = problem.field.type === 'hidden'
            ? (problem.field.name === 'date_of_birth' ? form.querySelector('[data-dob-text]') : form.querySelector('#startCameraBtn'))
            : problem.field;
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function () { target.focus({ preventScroll: true }); }, 250);
        }
    }

    function report(problems, wholeForm) {
        if (!problems.length) { summary.classList.add('d-none'); return; }
        var first = problems[0];
        if (wholeForm && window.admissionWizard && window.admissionWizard.current() !== first.step) {
            window.admissionWizard.go(first.step);
        }
        if (wholeForm) {
            var byStep = {};
            problems.forEach(function (p) { (byStep[p.step] = byStep[p.step] || []).push(p.label); });
            var html = '<strong>Please fix ' + problems.length + (problems.length === 1 ? ' field' : ' fields') + ' before submitting:</strong><ul class="mb-0 mt-1">';
            Object.keys(byStep).forEach(function (s) {
                html += '<li>' + stepName(+s) + ': ' + byStep[s].map(function (t) { return t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }).join(', ') + '</li>';
            });
            summary.innerHTML = html + '</ul>';
            summary.classList.remove('d-none');
        }
        focusFirst(first);
    }

    // "Next": check the current step first (runs before the page's own handler)
    form.addEventListener('click', function (e) {
        var btn = e.target.closest('.next-step');
        if (!btn) return;
        var current = window.admissionWizard ? window.admissionWizard.current() : steps().indexOf(btn.closest('.tab-pane'));
        var problems = validate(current);
        if (problems.length) {
            e.preventDefault();
            e.stopImmediatePropagation();
            report(problems, false);
        } else {
            summary.classList.add('d-none');
        }
    }, true);

    // Submit: check every step; nothing is sent until all are fine
    var submitting = false;
    form.addEventListener('submit', function (e) {
        var problems = validate(null);
        if (problems.length) {
            e.preventDefault();
            e.stopImmediatePropagation();
            report(problems, true);
            return;
        }
        if (submitting) { e.preventDefault(); return; } // no double submit
        submitting = true;
        var btn = form.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…'; }
    }, true);

    // Clear a field's error as soon as it is corrected
    form.addEventListener('input', function (e) { if (e.target.classList.contains('is-invalid') && !checkField(e.target)) clearError(e.target); });
    form.addEventListener('change', function (e) { if (e.target.name && !checkField(e.target)) clearError(e.target); });

    // Older records may hold CNICs / phone numbers as plain digits. Show them in the
    // format the form (and the server) expects, so they don't block saving.
    Object.keys(PATTERNS).forEach(function (name) {
        var f = form.querySelector('[name="' + name + '"]');
        if (!f || !f.value) return;
        var d = f.value.replace(/\D/g, '');
        if (/nic|b_form/.test(name) && d.length === 13) f.value = d.slice(0, 5) + '-' + d.slice(5, 12) + '-' + d.slice(12);
        if (/contact/.test(name) && d.length === 11 && d.indexOf('03') === 0) f.value = d.slice(0, 4) + '-' + d.slice(4);
    });

    // The browser's own required-field bubbles can't reach fields on other steps; we handle it instead.
    form.setAttribute('novalidate', 'novalidate');
})();
</script>
