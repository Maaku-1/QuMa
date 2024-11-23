let timeLimit = 0; // Initialize timeLimit to 0

window.onload = function () {
    const timerElement = document.getElementById('timer');
    timeLimit = parseInt(timerElement.getAttribute('data-time-limit'), 10); // Get the time limit from the element's attribute
    startTimer(timerElement);
};

function startTimer(timerElement) {
    const timerInterval = setInterval(() => {
        if (timeLimit <= 0) {
            clearInterval(timerInterval);
            timerElement.textContent = "Time's Up!";
            document.getElementById('quizForm').submit(); // Auto-submit the form
        } else {
            const hours = Math.floor(timeLimit / 3600); // Calculate hours
            const minutes = Math.floor((timeLimit % 3600) / 60); // Calculate remaining minutes
            const seconds = timeLimit % 60; // Calculate remaining seconds

            // Format the time string
            const formattedTime = `${hours > 0 ? hours + ':' : ''}${hours > 0 && minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            timerElement.textContent = `Time Remaining: ${formattedTime}`;

            timeLimit--;
        }
    }, 1000);
}
