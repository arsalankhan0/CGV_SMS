function generateResult(event) 
{
    event.preventDefault();

    function calculateTotalMaxObtPercentage(maxId, obtId) 
    {
        let maxInputs = document.querySelectorAll(maxId);
        let obtInputs = document.querySelectorAll(obtId);
        let totalMaxMarks = 0;
        let totalObtMarks = 0;

        maxInputs.forEach((input, index) => {
            let maxMarks = parseInt(input.value) || 0;
            let obtMarks = parseInt(obtInputs[index].value) || 0;

            totalMaxMarks += maxMarks;
            totalObtMarks += obtMarks;
        });

        let percentage = totalMaxMarks !== 0 ? (totalObtMarks / totalMaxMarks) * 100 : 0;

        return {
            totalMaxMarks,
            totalObtMarks,
            percentage
        };
    }

    try 
    {
        // Calculate and update the totals and percentage
        let thResult = calculateTotalMaxObtPercentage('#th-max-assign', '#th-obt-assign');
        let pracResult = calculateTotalMaxObtPercentage('#prac-max-assign', '#prac-obt-assign');
        let vivaResult = calculateTotalMaxObtPercentage('#viva-max-assign', '#viva-obt-assign');

        // Update the totals in the table
        document.getElementById('th-max-marks').textContent = thResult.totalMaxMarks;
        document.getElementById('th-obt-marks').textContent = thResult.totalObtMarks;
        document.getElementById('prac-max-marks').textContent = pracResult.totalMaxMarks;
        document.getElementById('prac-obt-marks').textContent = pracResult.totalObtMarks;
        document.getElementById('viva-max-marks').textContent = vivaResult.totalMaxMarks;
        document.getElementById('viva-obt-marks').textContent = vivaResult.totalObtMarks;

        // Calculate and update the grand total and percentage
        let grandTotalMaxMarks = thResult.totalMaxMarks + pracResult.totalMaxMarks + vivaResult.totalMaxMarks;
        let grandTotalObtMarks = thResult.totalObtMarks + pracResult.totalObtMarks + vivaResult.totalObtMarks;
        let grandPercentage = grandTotalMaxMarks !== 0 ? (grandTotalObtMarks / grandTotalMaxMarks) * 100 : 0;

        // Update the grand totals and percentage in the table
        document.getElementById('total-max-marks').textContent = grandTotalMaxMarks;
        document.getElementById('total-obt-marks').textContent = grandTotalObtMarks;
        document.getElementById('percentage').textContent = grandPercentage.toFixed(2) + '%';

        if (grandPercentage > 100) 
        {
            alert('Percentage cannot be more than 100%. Please check your input values.');
        }
    } 
    catch (error) 
    {
        // console.error('An error occurred:', error);
        alert('An error occurred while generating results. Please try again or check your input values.');
    }
}
