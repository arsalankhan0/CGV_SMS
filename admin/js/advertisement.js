document.getElementById('adSwitch').addEventListener('change', function() {
    let label = document.getElementById('adSwitchLabel');
    label.textContent = (this.checked) ? 'Hide Advertisement Banner on Homepage' : 'Show Advertisement Banner on Homepage';

    let showAd = this.checked ? 1 : 0;
    updateIsDisplayed(showAd);
});

function updateIsDisplayed(showAd) {
    let xhr = new XMLHttpRequest();
    xhr.open('POST', '../ajax/advertisement/ad_switch.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);
                showAlert(response.status, response.message);
            } else {
                console.error('Error:', xhr.status);
                showAlert('danger', 'An error occurred while updating the advertisement status.');
            }
        }
    };
    xhr.send('showAd=' + encodeURIComponent(showAd));
}

function showAlert(type, message) {
    let alertBox = document.createElement('div');
    alertBox.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible`;
    alertBox.innerHTML = `
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        ${message}
    `;
    document.querySelector('.card-body').prepend(alertBox);
    setTimeout(() => alertBox.remove(), 5000);
}

document.getElementById('uploadButton').addEventListener('click', function() {
    let formData = new FormData();
    let fileInput = document.getElementById('customFile');
    let file = fileInput.files[0];

    if (file) {
        formData.append('banner', file);
    }

    let xhr = new XMLHttpRequest();
    xhr.open('POST', '../ajax/advertisement/ad_upload.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);
                showAlert(response.status, response.message);
            } else {
                console.error('Error:', xhr.status);
                showAlert('danger', 'An error occurred while uploading the advertisement banner.');
            }
        }
    };
    xhr.send(formData);
});

function previewImage(input) {
    let file = input.files[0];
    let reader = new FileReader();
    
    reader.onload = (e) => {
        let imgPreview = document.getElementById('imagePreview');
        imgPreview.innerHTML = '<span>Selected Banner</span><img src="' + e.target.result + '" alt="Preview" class="img-fluid">';
    }
    reader.readAsDataURL(file);
}
