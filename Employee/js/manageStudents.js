function getSelectedSessionStudents(page = 1) {
    let selectedSession = document.getElementById("session").value;
    let selectedSort = document.getElementById("sort").value;

    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // We Update the content of student-list-container
            document.getElementById("student-list-container").innerHTML = xhr.responseText;
        }
    };
    
    // Use get_students.php file with the selected session ID, sort order, and page number
    xhr.open("GET", "../ajax/students/get_students.php?session_id=" + selectedSession + "&sort=" + selectedSort + "&page=" + page, true);
    xhr.send();
}
window.onload = getSelectedSessionStudents;

function changePage(page, totalPages) {
    if (page < 1 || page > totalPages) {
        return;
    }
    getSelectedSessionStudents(page);
}

function setDeleteId(id) {
    document.getElementById('studentID').value = id;
}
