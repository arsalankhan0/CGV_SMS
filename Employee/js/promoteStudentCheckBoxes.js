document.addEventListener('DOMContentLoaded', () => {
        
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const checkAllCheckbox = document.getElementById('checkAll');

    checkAllCheckbox.title = checkAllCheckbox.checked ? "Uncheck all" : "Check All";

    // For checking/unchecking all checkboxes with the overall checkbox
    checkAllCheckbox.addEventListener('change', () => {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = checkAllCheckbox.checked;
        });

        checkAllCheckbox.title = checkAllCheckbox.checked ? "Uncheck all" : "Check All";
    });

    // For unchecking the overall checkbox if any individual checkbox is unchecked
    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            checkAllCheckbox.checked = document.querySelectorAll('.student-checkbox:checked').length === checkboxes.length;

            checkAllCheckbox.title = checkAllCheckbox.checked ? "Uncheck all" : "Check All";
        });
    });
});