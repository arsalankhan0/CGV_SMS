// For printing the report card in another tab
document.getElementById('print-btn').addEventListener('click', () => {

    let printWindow = window.open('', '_blank');

    if(printWindow)
    {
            printWindow.document.write('<html><head><link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css"><link rel="stylesheet" href="css/style.css" /><title>Print</title>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(document.getElementById('report-card').outerHTML);
            printWindow.document.write('</body></html>');
            setTimeout(function () {
                printWindow.print();

                printWindow.close();
            }, 500);
        }
    }
);