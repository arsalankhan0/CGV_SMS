  // Function to update all max marks input fields of a specific subject
  function updateMaxMarksFields(subjectID, newValue) {
    let maxMarksInputFields = document.querySelectorAll('input[name^="SubMaxMarks"][data-subject-id="' + subjectID + '"]');
    let CCMaxMarksInputFields = document.querySelectorAll('input[name^="CoCurricularMaxMarks"][data-subject-id="' + subjectID + '"]');
    
    // Loop through each input field and update its value
    maxMarksInputFields.forEach(function(inputField) {
        inputField.value = newValue;
    });
    CCMaxMarksInputFields.forEach(function(inputField) {
        inputField.value = newValue;
    });
}

// Add event listeners to all max marks input fields to trigger the update function
let maxMarksInputs = document.querySelectorAll('.max-marks-input');
maxMarksInputs.forEach(function(input) {
    input.addEventListener('input', function(event) {
        let subjectID = event.target.getAttribute('data-subject-id');
        let newValue = event.target.value;
        updateMaxMarksFields(subjectID, newValue);
    });
});


// Function to handle input event on marks obtained input fields
function handleMarksObtainedInput(event) {
    let marksObtained = parseFloat(event.target.value);
    let maxMarksInputField = event.target.parentNode.previousElementSibling.querySelector('.max-marks-input');
    let maxMarks = parseFloat(maxMarksInputField.value);

    if (marksObtained > maxMarks) {
        event.target.classList.add('error');
        let errorMessage = event.target.parentNode.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.innerHTML = 'Marks obtained cannot exceed maximum marks';
            errorMessage.style.display = 'block';
        }
    } else {
        event.target.classList.remove('error');
        let errorMessage = event.target.parentNode.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }
}

// Function to validate marks obtained inputs before form submission
function validateFormBeforeSubmit() {
    let marksObtainedInputs = document.querySelectorAll('.marks-obtained-input');
    let isValid = true;

    marksObtainedInputs.forEach(function(input) {
        let marksObtained = parseFloat(input.value);
        let maxMarksInputField = input.parentNode.previousElementSibling.querySelector('.max-marks-input');
        let maxMarks = parseFloat(maxMarksInputField.value);

        if (marksObtained > maxMarks) {
            input.classList.add('error');
            let errorMessage = input.parentNode.querySelector('.error-message');
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
let marksObtainedInputs = document.querySelectorAll('.marks-obtained-input');
marksObtainedInputs.forEach(function(input) {
    input.addEventListener('input', handleMarksObtainedInput);
});

// Add event listener to the form submission
let form = document.querySelector('form');
form.addEventListener('submit', function(event) {
    if (!validateFormBeforeSubmit()) {
        event.preventDefault();
    }
});



// -----------For searching functionality----------------
let resultContainer = document.getElementById('search-result-container');
let result = document.getElementById('search-result');
let closeResult = document.getElementById('close-search-result');

closeResult.addEventListener('click', () => {
    resultContainer.style.display = "none";
    result.innerText = "";
});

document.getElementById('search-btn').addEventListener('click', function() {

        const searchInput = document.getElementById('search-input').value.toLowerCase().trim();
        const studentForms = document.querySelectorAll('form.forms-sample');

        if(searchInput.length === 0)
        {
            resultContainer.style.display = "block";
            result.innerText = "Please type name or Roll No to search!";
            return;
        }

        let found = false;

        studentForms.forEach(form => {
            const rollNo = form.querySelector('.roll-no span').textContent.toLowerCase().trim();
            const name = form.querySelector('.student-name span').textContent.toLowerCase().trim();

            if (rollNo === searchInput || name === searchInput) {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                form.style.border = '2px solid blue';
                setTimeout(() => form.style.border = '', 2000);
                found = true;
            }
        });
        if (!found) {
            resultContainer.style.display = "block";
            result.innerText = "No such Student Found";
        }
        else
        {
            resultContainer.style.display = "none";
            result.innerText = "";    
        }
    
});





