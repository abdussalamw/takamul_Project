/**
 * program-form.js - Shared JavaScript for Program Forms
 * Used by: submit_program.php, admin/add_program.php, admin/edit_program.php
 */

(function() {
    'use strict';

    // ============================================================
    // Organizer Select Logic
    // ============================================================
    function setupOrganizerSelector(organizersData) {
        const orgSelect = document.getElementById('organizer_id');
        if (!orgSelect) return;

        const nameGroup = document.getElementById('organizer_name_group');
        const phoneGroup = document.getElementById('entry_officer_phone_group');
        const orgNameInput = document.getElementById('organizer_name');
        const orgDeptInput = document.getElementById('organizer_department');
        const entryNameInput = document.getElementById('entry_officer_name');
        const entryPhoneInput = document.getElementById('entry_officer_phone');

        function adjustLayout(val) {
            // Both fields are now statically displayed and half-width to allow editing organizer names.
            if (orgNameInput) {
                orgNameInput.setAttribute('required', 'required');
            }
            if (nameGroup) {
                nameGroup.style.display = 'block';
            }
        }

        function populateOrganizerFields(val) {
            if (val === 'new') {
                orgNameInput.value = '';
                if (orgDeptInput) orgDeptInput.value = '';
                if (entryNameInput) entryNameInput.value = '';
                if (entryPhoneInput) entryPhoneInput.value = '';
            } else if (val !== '' && organizersData) {
                const selectedOrg = organizersData.find(function(org) {
                    return org.id == val;
                });
                if (selectedOrg) {
                    orgNameInput.value = selectedOrg.name || '';
                    if (orgDeptInput) orgDeptInput.value = selectedOrg.sub_name || selectedOrg.department || '';
                    if (entryNameInput) entryNameInput.value = selectedOrg.communication_officer_name || selectedOrg.entry_officer_name || '';
                    if (entryPhoneInput) entryPhoneInput.value = selectedOrg.communication_officer_phone || selectedOrg.entry_officer_phone || '';
                }
            } else {
                orgNameInput.value = '';
                if (orgDeptInput) orgDeptInput.value = '';
                if (entryNameInput) entryNameInput.value = '';
                if (entryPhoneInput) entryPhoneInput.value = '';
            }
        }

        orgSelect.addEventListener('change', function() {
            adjustLayout(this.value);
            populateOrganizerFields(this.value);
        });

        // Initialize on page load
        adjustLayout(orgSelect.value);
        if (orgSelect.value && orgSelect.value !== 'new' && orgNameInput.value === '') {
            populateOrganizerFields(orgSelect.value);
        }
    }

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

            if (options.organizersData !== undefined) {
                setupOrganizerSelector(options.organizersData);
            }

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