"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var numberInputs = document.querySelectorAll('input[type="number"]');
  numberInputs.forEach(function (input) {
    input.addEventListener('keydown', function (event) {
      // Prevent typing 'e', 'E', '+', '-', and other non-numeric characters
      if (event.key === 'e' || event.key === 'E' || event.key === '+' || event.key === '-' || event.key === '.') {
        event.preventDefault();
      }
    });
  });
});