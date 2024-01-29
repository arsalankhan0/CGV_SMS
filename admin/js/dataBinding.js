const inputSub = document.getElementById('input-subject')
const subName = document.getElementById('subject-name')

inputSub.addEventListener('keyup', (e) => {
    subName.innerHTML = e.target.value;
})