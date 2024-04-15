document.getElementById("mobnum").addEventListener("input", function(event) {
    let inputValue = event.target.value.replace(/\D/g, '');
    let formattedValue = '';
    
    if (inputValue.length > 0) {
        formattedValue = inputValue.slice(0, 10);
        if (inputValue.startsWith('01') && inputValue.length > 4) {
            formattedValue = inputValue.slice(0, 4) + '-' + inputValue.slice(4, 11);
        }
    }
    
    event.target.value = formattedValue;
    event.target.setAttribute("pattern", inputValue.startsWith('01') ? "\\d{4}-\\d{7}" : "\\d{10}");
});