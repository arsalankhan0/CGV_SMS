  // Function to get and display the student list for the selected session
function getSelectedSessionStudents(page = 1) {
    let selectedSession = document.getElementById("session").value;

    // AJAX request
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Update the content of student-list-container
            document.getElementById("student-list-container").innerHTML = xhr.responseText;
        }
    };
    
    // Use get_students.php with the selected session ID and page number
    xhr.open("GET", "../ajax/students/get_students.php?session_id=" + selectedSession + "&page=" + page, true);
    xhr.send();
}
window.onload = getSelectedSessionStudents;

// Function to handle page change
function changePage(page) {
    getSelectedSessionStudents(page);
}

function setDeleteId(id) {
    document.getElementById('studentID').value = id;
}