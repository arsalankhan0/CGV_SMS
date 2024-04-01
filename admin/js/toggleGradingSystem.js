document.addEventListener('DOMContentLoaded', function() {
    // Get the checkbox element
    var gradingCheckbox = document.getElementById('optionalGrading');

    // Get the table head and table body
    var table = document.getElementById('hide-table');

    // Hide or show the table rows based on the checkbox state
    function toggleRows() 
    {
        if (gradingCheckbox.checked) 
        {
            table.style.display = 'none';
        } 
        else 
        {
            table.style.display = 'block';
        }
    }

    // Call the function to toggle rows when the checkbox state changes
    gradingCheckbox.addEventListener('change', toggleRows);

    // Initially, toggle rows based on the checkbox state
    toggleRows();
});
