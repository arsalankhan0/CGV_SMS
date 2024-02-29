setTimeout(() => {
    let successAlert = document.getElementById('success-alert');
    let dangerAlert = document.getElementById('danger-alert');
    
    if (successAlert) 
    {
        successAlert.style.display = 'none';
    }
    if (dangerAlert) 
    {
        dangerAlert.style.display = 'none';
    }
}, 6000);