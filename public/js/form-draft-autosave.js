/**
 * Sauvegarde automatique des formulaires dans localStorage.
 * Restaure la saisie si l'utilisateur quitte la page puis revient.
 */
(function () {
    'use strict';

    var SKIP_NAMES = ['_token', '_method'];
    var PREFIX = 'form_draft_';

    function getScope() {
        var meta = document.querySelector('meta[name="form-draft-scope"]');
        return meta ? meta.getAttribute('content') : 'global';
    }

    function hasValidationErrors() {
        var meta = document.querySelector('meta[name="form-has-errors"]');
        return meta && meta.getAttribute('content') === '1';
    }

    function shouldClearDraftsOnLoad() {
        var meta = document.querySelector('meta[name="form-draft-clear"]');
        return meta && meta.getAttribute('content') === '1';
    }

    function simpleHash(str) {
        var h = 0;
        for (var i = 0; i < str.length; i++) {
            h = ((h << 5) - h) + str.charCodeAt(i);
            h |= 0;
        }
        return Math.abs(h).toString(36);
    }

    function draftKey(form) {
        var path = form.getAttribute('data-draft-path') || window.location.pathname;
        var id = form.id || form.getAttribute('action') || 'form';
        return PREFIX + getScope() + '_' + path.replace(/\//g, '_') + '_' + simpleHash(id);
    }

    function collectFormData(form) {
        var data = {};

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name || el.disabled) return;
            if (el.getAttribute('data-draft-exclude') === 'true') return;
            if (SKIP_NAMES.indexOf(el.name) !== -1) return;
            if (el.type === 'file' || el.type === 'password') return;

            if (el.type === 'radio') {
                if (el.checked) data[el.name] = el.value;
            } else if (el.type === 'checkbox') {
                if (el.name.slice(-2) === '[]') {
                    if (!data[el.name]) data[el.name] = [];
                    if (el.checked) data[el.name].push(el.value);
                } else {
                    data[el.name] = el.checked;
                }
            } else if (el.tagName === 'SELECT' && el.multiple) {
                data[el.name] = Array.from(el.selectedOptions).map(function (o) { return o.value; });
            } else {
                data[el.name] = el.value;
            }
        });

        return data;
    }

    function dataHasContent(data) {
        return Object.keys(data).some(function (key) {
            if (SKIP_NAMES.indexOf(key) !== -1) return false;
            var value = data[key];
            if (Array.isArray(value)) return value.length > 0;
            if (typeof value === 'boolean') return value;
            return value !== null && value !== undefined && String(value).trim() !== '';
        });
    }

    function applyFormData(form, data) {
        Object.keys(data).forEach(function (name) {
            var value = data[name];
            var fields = form.querySelectorAll('[name="' + name.replace(/"/g, '\\"') + '"]');
            if (!fields.length) return;

            var first = fields[0];
            if (first.getAttribute('data-draft-exclude') === 'true') return;
            if (first.type === 'radio') {
                fields.forEach(function (r) { r.checked = r.value === value; });
            } else if (first.type === 'checkbox') {
                if (Array.isArray(value)) {
                    fields.forEach(function (c) { c.checked = value.indexOf(c.value) !== -1; });
                } else {
                    first.checked = !!value;
                }
            } else if (first.tagName === 'SELECT' && first.multiple) {
                Array.from(first.options).forEach(function (o) {
                    o.selected = Array.isArray(value) && value.indexOf(o.value) !== -1;
                });
            } else {
                first.value = value == null ? '' : value;
            }
        });
    }

    function noticeId(key) {
        return 'form-draft-notice-' + key.replace(/\W/g, '_');
    }

    function showRestoreNotice(form, key, onClear) {
        var nid = noticeId(key);
        if (document.getElementById(nid)) return;

        var container = form.closest('.card-body') || form.parentElement;
        if (!container) return;

        var notice = document.createElement('div');
        notice.id = nid;
        notice.className = 'alert alert-info alert-dismissible fade show py-2 small mb-3 form-draft-notice';
        notice.innerHTML = '<i class="fas fa-save me-1"></i> Saisie restaurée — vous pouvez continuer où vous vous étiez arrêté.'
            + ' <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline form-draft-clear-btn">Effacer</button>'
            + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        container.insertBefore(notice, container.firstChild);

        notice.querySelector('.form-draft-clear-btn').addEventListener('click', function () {
            localStorage.removeItem(key);
            notice.remove();
            form.reset();
            if (typeof onClear === 'function') onClear();
            form.dispatchEvent(new CustomEvent('form-draft-cleared', { bubbles: true, detail: { form: form, key: key } }));
        });
    }

    function clearAllDraftsForScope() {
        var scope = getScope();
        var prefix = PREFIX + scope + '_';
        try {
            Object.keys(localStorage).forEach(function (k) {
                if (k.indexOf(prefix) === 0) localStorage.removeItem(k);
            });
        } catch (e) {}
    }

    function initForm(form) {
        if (form.getAttribute('data-no-draft') === 'true') return;
        if (form.closest('.modal')) return;
        if (form.dataset.draftInitialized === '1') return;

        var method = (form.getAttribute('method') || 'get').toLowerCase();
        var isPost = method === 'post' || form.querySelector('input[name="_token"]');
        if (!isPost) return;

        form.dataset.draftInitialized = '1';

        var key = draftKey(form);
        var formPath = form.getAttribute('data-draft-path') || window.location.pathname;

        if (!hasValidationErrors()) {
            try {
                var raw = localStorage.getItem(key);
                if (raw) {
                    var parsed = JSON.parse(raw);
                    if (parsed.data && dataHasContent(parsed.data)) {
                        applyFormData(form, parsed.data);
                        showRestoreNotice(form, key);
                        form.dispatchEvent(new CustomEvent('form-draft-restored', {
                            bubbles: true,
                            detail: { form: form, data: parsed.data, formPath: formPath, key: key }
                        }));
                    }
                }
            } catch (e) {}
        }

        var timer;
        function scheduleSave() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (hasValidationErrors()) return;
                try {
                    var data = collectFormData(form);
                    if (!dataHasContent(data)) {
                        localStorage.removeItem(key);
                        return;
                    }
                    localStorage.setItem(key, JSON.stringify({ savedAt: Date.now(), formPath: formPath, data: data }));
                } catch (e) {}
            }, 350);
        }

        form.addEventListener('input', scheduleSave);
        form.addEventListener('change', scheduleSave);

        form.addEventListener('submit', function () {
            if (form.checkValidity()) {
                localStorage.removeItem(key);
            }
        });
    }

    function initAllForms() {
        if (shouldClearDraftsOnLoad()) {
            clearAllDraftsForScope();
        }

        document.querySelectorAll('form').forEach(function (form) {
            initForm(form);
        });
    }

    window.FormDraftAutosave = {
        initForm: initForm,
        clearAllDraftsForScope: clearAllDraftsForScope,
        collectFormData: collectFormData,
        applyFormData: applyFormData,
        wasRestored: function () {
            return document.querySelector('.form-draft-notice') !== null;
        }
    };

    function boot() {
        initAllForms();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        setTimeout(boot, 0);
    }
})();
