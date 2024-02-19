// Function to dynamically populate sections based on the selected class
    function populateSections() 
    {
        var selectedClass = document.getElementById('class').value;
        var sectionDropdown = document.getElementById('section');

        sectionDropdown.innerHTML = '';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'getSections.php?class=' + selectedClass, true);

        xhr.onload = function () 
        {
            if (xhr.status === 200) 
            {
                var sections = JSON.parse(xhr.responseText);

                sectionDropdown.innerHTML = '';

                sections.forEach(function (section) 
                {
                    var option = document.createElement('option');
                    option.value = section.trim();
                    option.text = section.trim();
                    sectionDropdown.appendChild(option);
                });
            }
        };

        xhr.send();
    }

    document.getElementById('class').addEventListener('change', populateSections);

    populateSections();