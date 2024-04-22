 // FOR SHOWING AND HIDING THE SYLLABUS AND NOTES
 document.addEventListener('DOMContentLoaded', function() {
    // Hide all resource content initially
    var resourceContents = document.querySelectorAll('.resource-content');
    resourceContents.forEach(function(content) {
        content.style.display = 'none';
    });

    // Show the corresponding resource content when its link is clicked
    var resourceLinks = document.querySelectorAll('.resource-menu a');
    resourceLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            var target = this.getAttribute('href');
            resourceContents.forEach(function(content) {
                content.style.display = 'none';
            });
            document.querySelector(target).style.display = 'block';
        });
    });
});

// FOR DISPLAYING THE SUBJECT OPTIONS AS PER THE SELECTED CLASS
document.addEventListener('DOMContentLoaded', function () 
{
    document.getElementById('classDropdown').addEventListener('change', function () {
        var classId = this.value;
        if (classId !== '') {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'getSubjects.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var subjectDropdown = document.getElementById('subjectDropdown');
                    subjectDropdown.innerHTML = ''; 

                    let defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Select Subject';
                    subjectDropdown.appendChild(defaultOption);

                    response.forEach(function (value) {
                        var option = document.createElement('option');
                        option.value = value.ID;
                        option.textContent = value.SubjectName;
                        subjectDropdown.appendChild(option);
                    });
                } else {
                    console.error('Request failed. Status: ' + xhr.status);
                }
            };
            xhr.send('classId=' + encodeURIComponent(classId));
        } else {
            document.getElementById('subjectDropdown').innerHTML = ''; 
        }
    });

    document.getElementById('classDropdown').addEventListener('change', function () {
        updateNotesTable();
    });

    document.getElementById('subjectDropdown').addEventListener('change', function () {
        updateNotesTable();
    });
});


// FOR DISPLAYING TABLE AS PER THE SELECTED CLASS AND SUBJECT
function updateNotesTable() {
    var classId = document.getElementById('classDropdown').value;
    var subjectId = document.getElementById('subjectDropdown').value;

    if (classId !== '' && subjectId !== '') {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'getNotes.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                var notes = JSON.parse(xhr.responseText);
                if (notes.length > 0) {
                    displayNotes(notes);
                } else {
                    showNoRecordsMessage();
                }
            } else {
                console.error('Request failed. Status: ' + xhr.status);
            }
        };
        xhr.send('classId=' + encodeURIComponent(classId) + '&subjectId=' + encodeURIComponent(subjectId));
    } else {
        // Clear the table if either class or subject is not selected
        clearNotesTable();
    }
}

function displayNotes(notes) {
    var tableBody = document.querySelector('.notes-list tbody');
    tableBody.innerHTML = ''; // Clear existing table rows

    if (notes.length === 0) {
        var row = tableBody.insertRow();
        var noRecordsCell = row.insertCell();
        noRecordsCell.colSpan = 3;
        noRecordsCell.textContent = 'No records found';
    } else {
        notes.forEach(function (note, index) {
            var row = tableBody.insertRow();
            var snoCell = row.insertCell(0);
            var titleCell = row.insertCell(1);
            var actionCell = row.insertCell(2);

            snoCell.textContent = index + 1;
            titleCell.textContent = note.Title;

            var viewLink = document.createElement('a');
            viewLink.href = 'admin/notes/' + note.Notes;
            viewLink.textContent = 'View';
            // viewLink.className = '';
            viewLink.target = '_blank';
            actionCell.appendChild(viewLink);
        });
    }
}


function clearNotesTable() {
    var tableBody = document.getElementById('notesTableBody');
    tableBody.innerHTML = ''; // Clear existing table rows
}

function showNoRecordsMessage() {
    var tableBody = document.getElementById('notesTableBody');
    tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No records found</td></tr>';
}