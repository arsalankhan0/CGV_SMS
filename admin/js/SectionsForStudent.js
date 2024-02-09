document.getElementById('stuclass').addEventListener('change', function() {
    var classId = this.value;
    var sectionDropdown = document.getElementById('stusection');

    sectionDropdown.innerHTML = '';

    if (classId !== '') 
    {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_sections.php?classId=' + classId, true);
        xhr.onreadystatechange = () => {
            if (xhr.readyState == 4 && xhr.status == 200) 
            {
                var sections = JSON.parse(xhr.responseText);

                sections.forEach((section) => {
                    var option = document.createElement('option');
                    option.value = section;
                    option.text = section;
                    sectionDropdown.add(option);
                });
            }
        };
        xhr.send();
    }
});