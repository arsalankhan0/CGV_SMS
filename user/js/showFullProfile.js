document.addEventListener('DOMContentLoaded', function () {

    let profileImage = document.getElementById('profileImage');

    let modal = document.getElementById('profileImageModal');
    let largeImage = document.getElementById('largeProfileImage');

    profileImage.addEventListener('click', function () {
        largeImage.src = this.src;
        modal.style.display = 'flex';
    });

    modal.addEventListener('click', function () {
        modal.style.display = 'none';
    });
});