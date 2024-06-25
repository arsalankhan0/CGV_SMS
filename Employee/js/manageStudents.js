function getSelectedSessionStudents(page = 1) {
    let selectedSession = document.getElementById("session").value;
    let selectedClass = document.getElementById("class").value;


    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById("student-list-container").innerHTML = xhr.responseText;
        }
    };
    
    xhr.open("GET", "../ajax/students/get_students.php?session_id=" + selectedSession + "&class=" + selectedClass + "&page=" + page, true);
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
