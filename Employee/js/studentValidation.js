document.addEventListener('DOMContentLoaded', function() 
{
    // Get references to form and input elements
    const form = document.querySelector('form');
    const stunameInput = document.getElementsByName('stuname')[0];
    const genderInput = document.getElementsByName('gender')[0];
    const stuclassInput = document.getElementById('stuclass');
    const stusectionInput = document.getElementById('stusection');
    const stuRollNoInput = document.getElementsByName('stuRollNo')[0];
    const fnameInput = document.getElementsByName('fname')[0];
    const connumInput = document.getElementsByName('connum')[0];
    const addressInput = document.getElementsByName('address')[0];
    const codeInput = document.getElementById('code');
    const stuidInput = document.getElementById('stuid');
    const passwordInput = document.getElementById('password');

    // Add event listeners for validation
    stunameInput.addEventListener('keyup', validateStudentName);
    genderInput.addEventListener('change', validateGender);
    stuclassInput.addEventListener('change', validateStudentClass);
    stusectionInput.addEventListener('change', validateStudentSection);
    stuRollNoInput.addEventListener('keyup', validateStudentRollNo);
    fnameInput.addEventListener('keyup', validateFname);
    connumInput.addEventListener('keyup', validateContactNumber);
    addressInput.addEventListener('keyup', validateAddress);
    codeInput.addEventListener('keyup', validateCode);
    stuidInput.addEventListener('keyup', function() {
        checkAvailability('stuid', stuidInput.value);
    });
    passwordInput.addEventListener('keyup', validatePassword);

    // Prevent form submission if error occurred
    form.addEventListener('submit', function(event) {
        if (!validateForm()) {
            event.preventDefault();
        }
    });

    // Validate Student Name
    function validateStudentName() 
    {
        const name = stunameInput.value.trim();
        const namePattern = /^[a-zA-Z\s]+$/;
        if (name === '') {
            setError(stunameInput, 'Student Name is required!');
        } else if (!namePattern.test(name)) {
            setError(stunameInput, 'Student Name should not contain numbers or special characters!');
        } else {
            clearError(stunameInput);
        }
    }
    // Validate Code No.
    function validateCode() 
    {
        const code = codeInput.value.trim();
        if (code === '') {
            setError(codeInput, 'Code No. is required!');
        } else {
            clearError(codeInput);
        }
    }

    // Validate Gender
    function validateGender() {
        const gender = genderInput.value;
        if (gender === '') {
            setError(genderInput, 'Gender is required!');
        } else {
            clearError(genderInput);
        }
    }

    // Validate Student Class
    function validateStudentClass() {
        const studentClass = stuclassInput.value;
        if (studentClass === '') {
            setError(stuclassInput, 'Student Class is required!');
        } else {
            clearError(stuclassInput);
        }
    }

    // Validate Section
    function validateStudentSection() {
        const studentSection = stusectionInput.value;
        if (studentSection === '') {
            setError(stusectionInput, 'Student Section is required!');
        } else {
            clearError(stusectionInput);
        }
    }

    // Validate RollNo
    function validateStudentRollNo() {
        const rollNo = stuRollNoInput.value;
        if (rollNo === '') {
            setError(stuRollNoInput, 'Student Roll No is required!');
        } else if (isNaN(rollNo) || rollNo < 0) {
            setError(stuRollNoInput, 'Student Roll No must be a positive number!');
        } else {
            clearError(stuRollNoInput);
        }
    }

    // Validate Father's Name
    function validateFname() {
        const fname = fnameInput.value.trim();
        const fNamePattern = /^[a-zA-Z\s]+$/;
        if (fname === '') {
            setError(fnameInput, 'Father\'s/Guardian\'s Name is required!');
        } else if (!fNamePattern.test(fname)) {
            setError(fnameInput, 'Father\'s/Guardian\'s Name should not contain numbers or special characters!');
        } else {
            clearError(fnameInput);
        }
    }

    // Validate Contact Number
    function validateContactNumber() {
        const contactNumber = connumInput.value.trim();
        const contactNumberPattern = /^[0-9]{10}$/;
        if (contactNumber === '') {
            setError(connumInput, 'Contact Number is required!');
        } else if (!contactNumberPattern.test(contactNumber)) {
            setError(connumInput, 'Contact Number must be 10 digits!');
        } else {
            clearError(connumInput);
        }
    }

    // Validate Address
    function validateAddress() {
        const address = addressInput.value.trim();
        if (address === '') {
            setError(addressInput, 'Address is required!');
        } else {
            clearError(addressInput);
        }
    }

    // Validate Password
    function validatePassword() {
        const password = passwordInput.value;
        const passwordValidationMessage = document.getElementById('passwordValidationMessage');

        // Validate password length
        if (password.length < 8) {
            setError(passwordInput, 'Password must be at least 8 characters long!');
            passwordValidationMessage.textContent = 'Password must be at least 8 characters long!';
            return;
        }

        // Validate alphabetic character
        if (!/[a-zA-Z]/.test(password)) {
            setError(passwordInput, 'Password must contain at least one alphabetic character!');
            passwordValidationMessage.textContent = 'Password must contain at least one alphabetic character!';
            return;
        }

        // Validate number and special character
        if (!/\d/.test(password) || !/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            setError(passwordInput, 'Password must contain at least one number and one special character!');
            passwordValidationMessage.textContent = 'Password must contain at least one number and one special character!';
            return;
        }

        clearError(passwordInput);
        passwordValidationMessage.textContent = '';
    }

    // Validate Form
    function validateForm() {
        validateStudentName();
        validateGender();
        validateStudentClass();
        validateStudentSection();
        validateStudentRollNo();
        validateFname();
        validateContactNumber();
        validateAddress();
        validateCode();
        validatePassword();

        const errors = document.querySelectorAll('.form-control.is-invalid');
        return errors.length === 0;
    }

    // Set Error
    function setError(input, message) {
        input.classList.add('is-invalid');
        input.nextElementSibling.textContent = message;
    }

    // Clear Error
    function clearError(input) {
        input.classList.remove('is-invalid');
        input.nextElementSibling.textContent = '';
    }

    // Check Student ID exists or not
    function checkAvailability(type, value) {
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let stuidAvailability = document.getElementById('stuidAvailability');
                    stuidAvailability.innerHTML = xhr.responseText;
                    if (xhr.responseText === "Student ID already exists") {
                        stuidInput.style.borderColor = "red";
                        setError(stuidInput, 'Student ID already exists');
                    } else {
                        stuidInput.style.borderColor = "";
                        clearError(stuidInput);
                    }
                }
            }
        };
        xhr.open('POST', '../ajax/students/student_availability.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(type + '=' + encodeURIComponent(value));
    }
});
