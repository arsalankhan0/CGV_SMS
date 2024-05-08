document.addEventListener('DOMContentLoaded', function() 
        {
            var usernameInput = document.getElementById('username');
            var empidInput = document.getElementById('empid');

            usernameInput.addEventListener('keyup', function() {
                var username = usernameInput.value;
                checkAvailability('username', username);
            });

            empidInput.addEventListener('keyup', function() {
                var empid = empidInput.value;
                checkAvailability('empid', empid);
            });

            function checkAvailability(type, value) 
            {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() 
                {
                    if (xhr.readyState === XMLHttpRequest.DONE) 
                    {
                        if (xhr.status === 200) 
                        {
                            if (type === 'username') 
                            {
                                var usernameAvailability = document.getElementById('usernameAvailability');
                                usernameAvailability.innerHTML = xhr.responseText;
                                if (xhr.responseText === "Username already exists") 
                                {
                                    usernameInput.style.borderColor = "red";
                                } 
                                else 
                                {
                                    usernameInput.style.borderColor = "";
                                }
                            } 
                            else if (type === 'empid') 
                            {
                                var empidAvailability = document.getElementById('empidAvailability');
                                empidAvailability.innerHTML = xhr.responseText;
                                if (xhr.responseText === "Employee ID already exists") 
                                {
                                    empidInput.style.borderColor = "red";
                                } 
                                else 
                                {
                                    empidInput.style.borderColor = "";
                                }
                            }
                        }
                    }
                };
                xhr.open('POST', 'employee_availability.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send(type + '=' + encodeURIComponent(value));
            }
        });