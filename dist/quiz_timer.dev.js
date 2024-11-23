"use strict";

var timeLimit = 0; // Initialize timeLimit to 0

window.onload = function () {
  var timerElement = document.getElementById('timer');
  timeLimit = parseInt(timerElement.getAttribute('data-time-limit'), 10); // Get the time limit from the element's attribute

  startTimer(timerElement);
};

function startTimer(timerElement) {
  var timerInterval = setInterval(function () {
    if (timeLimit <= 0) {
      clearInterval(timerInterval);
      timerElement.textContent = "Time's Up!";
      document.getElementById('quizForm').submit(); // Auto-submit the form
    } else {
      var hours = Math.floor(timeLimit / 3600); // Calculate hours

      var minutes = Math.floor(timeLimit % 3600 / 60); // Calculate remaining minutes

      var seconds = timeLimit % 60; // Calculate remaining seconds
      // Format the time string

      var formattedTime = "".concat(hours > 0 ? hours + ':' : '').concat(hours > 0 && minutes < 10 ? '0' : '').concat(minutes, ":").concat(seconds < 10 ? '0' : '').concat(seconds);
      timerElement.textContent = "Time Remaining: ".concat(formattedTime);
      timeLimit--;
    }
  }, 1000);
}