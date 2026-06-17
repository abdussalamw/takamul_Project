/**
 * program-form.js - Shared JavaScript for Program Forms
 * Used by: submit_program.php, admin/add_program.php, admin/edit_program.php
 */

(function() {
    'use strict';

    // Organizer Select Logic removed - organizer is now a simple text input.

    // ============================================================
    // Program Type Logic
    // ============================================================
    function setupProgramTypeToggle() {
        const programType = document.getElementById('program_type');
        const otherGroup = document.getElementById('program_type_other_group');
        if (!programType || !otherGroup) return;

        programType.addEventListener('change', function() {
            otherGroup.style.display = this.value === 'أخرى' ? 'block' : 'none';
        });
    }

    // ============================================================
    // Is Free / Price Logic
    // ============================================================
    function setupIsFreeToggle() {
        const isFree = document.getElementById('is_free');
        const priceGroup = document.getElementById('price_group');
        const priceInput = document.getElementById('price');
        if (!isFree || !priceGroup || !priceInput) return;

        isFree.addEventListener('change', function() {
            if (this.value === '0') {
                priceGroup.style.display = 'block';
                priceInput.setAttribute('required', 'required');
            } else {
                priceGroup.style.display = 'none';
                priceInput.removeAttribute('required');
                priceInput.value = '';
            }
        });
    }

    // ============================================================
    // Age Group Logic
    // ============================================================
    function setupAgeGroupLogic() {
        const otherCheckbox = document.getElementById('age_group_other_checkbox');
        const otherInput = document.getElementById('age_group_other_input');
        if (!otherCheckbox || !otherInput) return;

        otherCheckbox.addEventListener('change', function() {
            otherInput.style.display = this.checked ? 'block' : 'none';
            if (this.checked) {
                otherInput.setAttribute('required', 'required');
            } else {
                otherInput.removeAttribute('required');
                otherInput.value = '';
            }
        });

        // Trigger initial state if other value exists
        if (otherInput.value.trim() !== '') {
            otherCheckbox.checked = true;
            otherInput.style.display = 'block';
            otherInput.setAttribute('required', 'required');
        }
    }

    // ============================================================
    // Form Validation (age group checkboxes)
    // ============================================================
    function setupFormValidation(formId) {
        var form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(e) {
            var ageCheckboxes = document.querySelectorAll('input[name="age_group[]"], #age_group_other_checkbox');
            var isChecked = false;
            ageCheckboxes.forEach(function(cb) {
                if (cb.checked) isChecked = true;
            });
            if (!isChecked) {
                e.preventDefault();
                alert('الرجاء اختيار الفئة المستهدفة.');
            }
        });
    }

    // ============================================================
    // Word Count Limiter (for description)
    // ============================================================
    function setupWordLimit(textareaId, maxWords) {
        var textarea = document.getElementById(textareaId);
        if (!textarea) return;

        textarea.addEventListener('input', function() {
            var words = this.value.trim().split(/\s+/);
            if (words.length > maxWords) {
                words = words.slice(0, maxWords);
                this.value = words.join(' ');
            }
        });
    }

    // ============================================================
    // Public API
    // ============================================================
    window.ProgramForm = {
        init: function(options) {
            options = options || {};


            if (options.formId) {
                setupFormValidation(options.formId);
            }

            setupProgramTypeToggle();
            setupIsFreeToggle();
            setupAgeGroupLogic();

            if (options.wordLimit) {
                setupWordLimit('description', options.wordLimit);
            }
        }
    };

})();