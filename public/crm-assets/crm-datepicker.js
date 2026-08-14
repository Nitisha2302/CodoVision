(function () {
    function bindDatePickers(root) {
        const scope = root || document;
        scope.querySelectorAll('input.crm-datepicker, input[type="date"], input[type="datetime-local"]').forEach(function (input) {
            if (input.dataset.crmPickerBound === '1') return;
            input.dataset.crmPickerBound = '1';
            input.classList.add('crm-datepicker');
            input.setAttribute('inputmode', 'none');
            input.setAttribute('autocomplete', 'off');

            // Block typing; allow calendar selection only.
            input.addEventListener('keydown', function (e) {
                // Allow Tab / Esc for accessibility.
                if (e.key === 'Tab' || e.key === 'Escape') return;
                e.preventDefault();
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
            });

            input.addEventListener('click', function () {
                if (typeof input.showPicker === 'function') {
                    try { input.showPicker(); } catch (err) {}
                }
            });

            input.addEventListener('focus', function () {
                if (typeof input.showPicker === 'function') {
                    try { input.showPicker(); } catch (err) {}
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindDatePickers(document);
    });

    document.addEventListener('livewire:navigated', function () {
        bindDatePickers(document);
    });

    document.addEventListener('livewire:init', function () {
        Livewire.hook('morph.updated', function ({ el }) {
            bindDatePickers(el);
        });
    });
})();
