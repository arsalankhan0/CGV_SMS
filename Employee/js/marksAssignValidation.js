  // Function to update all max marks input fields of a specific subject
  function updateMaxMarksFields(subjectID, newValue) {
    // Select all max marks input fields for the specific subject
    var maxMarksInputFields = document.querySelectorAll('input[name^="SubMaxMarks"][data-subject-id="' + subjectID + '"]');
    var CCMaxMarksInputFields = document.querySelectorAll('input[name^="CoCurricularMaxMarks"][data-subject-id="' + subjectID + '"]');
    
    // Loop through each input field and update its value
    maxMarksInputFields.forEach(function(inputField) {
        inputField.value = newValue;
    });
    CCMaxMarksInputFields.forEach(function(inputField) {
        inputField.value = newValue;
    });
}

// Add event listeners to all max marks input fields to trigger the update function
var maxMarksInputs = document.querySelectorAll('.max-marks-input');
maxMarksInputs.forEach(function(input) {
    input.addEventListener('input', function(event) {
        var subjectID = event.target.getAttribute('data-subject-id');
        var newValue = event.target.value;
        updateMaxMarksFields(subjectID, newValue);
    });
});


// Function to handle input event on marks obtained input fields
function handleMarksObtainedInput(event) {
    var marksObtained = parseFloat(event.target.value);
    var maxMarksInputField = event.target.parentNode.previousElementSibling.querySelector('.max-marks-input');
    var maxMarks = parseFloat(maxMarksInputField.value);

    if (marksObtained > maxMarks) {
        event.target.classList.add('error');
        var errorMessage = event.target.parentNode.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.innerHTML = 'Marks obtained cannot exceed maximum marks';
            errorMessage.style.display = 'block';
        }
    } else {
        event.target.classList.remove('error');
        var errorMessage = event.target.parentNode.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }
}

// Function to validate marks obtained inputs before form submission
function validateFormBeforeSubmit() {
    var marksObtainedInputs = document.querySelectorAll('.marks-obtained-input');
    var isValid = true;

    marksObtainedInputs.forEach(function(input) {
        var marksObtained = parseFloat(input.value);
        var maxMarksInputField = input.parentNode.previousElementSibling.querySelector('.max-marks-input');
        var maxMarks = parseFloat(maxMarksInputField.value);

        if (marksObtained > maxMarks) {
            input.classList.add('error');
            var errorMessage = input.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.innerHTML = 'Marks obtained cannot exceed maximum marks';
                errorMessage.style.display = 'block';
            }
            isValid = false;
        }
    });

    return isValid;
}

// Add event listeners to all marks obtained input fields to trigger the validation
var marksObtainedInputs = document.querySelectorAll('.marks-obtained-input');
marksObtainedInputs.forEach(function(input) {
    input.addEventListener('input', handleMarksObtainedInput);
});

// Add event listener to the form submission
var form = document.querySelector('form');
form.addEventListener('submit', function(event) {
    if (!validateFormBeforeSubmit()) {
        event.preventDefault(); // Prevent form submission
    }
});
