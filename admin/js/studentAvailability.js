document.addEventListener('DOMContentLoaded', function() 
      {
            let usernameInput = document.getElementById('uname');
            let stuidInput = document.getElementById('stuid');

            usernameInput.addEventListener('keyup', function() {
                var username = usernameInput.value;
                checkAvailability('uname', username);
            });

            stuidInput.addEventListener('keyup', function() {
                let stuid = stuidInput.value;
                checkAvailability('stuid', stuid);
            });

            function checkAvailability(type, value) 
            {
                let xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() 
                {
                    if (xhr.readyState === XMLHttpRequest.DONE) 
                    {
                        if (xhr.status === 200) 
                        {
                            if (type === 'uname') 
                            {
                                let usernameAvailability = document.getElementById('usernameAvailability');
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
                            else if (type === 'stuid') 
                            {
                                let stuidAvailability = document.getElementById('stuidAvailability');
                                stuidAvailability.innerHTML = xhr.responseText;
                                if (xhr.responseText === "Student ID already exists") 
                                {
                                    stuidInput.style.borderColor = "red";
                                } 
                                else 
                                {
                                    stuidInput.style.borderColor = "";
                                }
                            }
                        }
                    }
                };
                xhr.open('POST', 'student_availability.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send(type + '=' + encodeURIComponent(value));
            }
        });