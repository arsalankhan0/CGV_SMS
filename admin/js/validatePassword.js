function validatePassword() 
        {
            var password = document.getElementById('password').value;
            var passwordValidationMessage = document.getElementById('passwordValidationMessage');
            var passwordInput = document.getElementById('password');

            // Validate password length
            if (password.length < 8) {
                passwordValidationMessage.textContent = "Password must be at least 8 characters long!";
                passwordInput.style.borderColor = "red";
                return;
            }

            // Validate alphabetic character
            if (!/[a-zA-Z]/.test(password)) {
                passwordValidationMessage.textContent = "Password must contain at least one alphabetic character!";
                passwordInput.style.borderColor = "red";
                return;
            }

            // Validate number and special character
            if (!/\d/.test(password) || !/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                passwordValidationMessage.textContent = "Password must contain at least one number and one special character!";
                passwordInput.style.borderColor = "red";
                return;
            }

            passwordValidationMessage.textContent = "";
            passwordInput.style.borderColor = "";
        }