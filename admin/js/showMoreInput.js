document.addEventListener('DOMContentLoaded', () => {
    let roleDropdown = document.getElementById('employeeRole');

    let assignClassesSection = document.getElementById('assignClassesSection');
    let assignSubjectsSection = document.getElementById('assignSubjectsSection');
    
    let assignedClassesSelect = $('.js-example-basic-multiple[name="assignedClasses[]"]');
    let assignedSubjectsSelect = $('.js-example-basic-multiple[name="assignedSubjects[]"]');

    function toggleSections() {
        let selectedRole = roleDropdown.value;

        if (selectedRole === 'Teaching') {
            assignClassesSection.style.display = 'block';
            assignSubjectsSection.style.display = 'block';

            // Set the 'required' attribute for the select options
            assignedClassesSelect.setAttribute('required', true);
            assignedSubjectsSelect.setAttribute('required', true);
        } else {
            assignClassesSection.style.display = 'none';
            assignSubjectsSection.style.display = 'none';

            // Remove the 'required' attribute for the select options
            assignedClassesSelect.removeAttribute('required');
            assignedSubjectsSelect.removeAttribute('required');

            // Clear the selected options using Select2
            assignedClassesSelect.val(null).trigger('change');
            assignedSubjectsSelect.val(null).trigger('change');
        }
    }

    roleDropdown.addEventListener('change', toggleSections);

    toggleSections();
});