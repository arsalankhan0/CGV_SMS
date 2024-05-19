    // Function to get and display the student list for the selected session
    function getSelectedSessionStudents() 
    {
        var selectedSession = document.getElementById("session").value;

        // AJAX request
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Update the content of student-list-container
                document.getElementById("student-list-container").innerHTML = xhr.responseText;
            }
        };
        
        // Use get_students.php with the selected session ID
        xhr.open("GET", "../ajax/students/get_students.php?session_id=" + selectedSession, true);
        xhr.send();
    }

    // Call the function on page load to display the student list for the default selected session
    window.onload = getSelectedSessionStudents;


    function setDeleteId(id) 
    {
        document.getElementById('studentID').value = id;
    }