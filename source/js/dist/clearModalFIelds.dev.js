"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var modals = document.querySelectorAll('.modal');
  modals.forEach(function (modal) {
    modal.addEventListener('hidden.bs.modal', function () {
      // Clear all input fields within the modal
      var inputs = modal.querySelectorAll('input');
      inputs.forEach(function (input) {
        if (input.id === 'create-quiz-start-date' || input.id === 'create-quiz-end-date') {
          input.value = 'dd/mm/yyyy --:-- --'; // Reset to default value
        } else {
          input.value = ''; // Clear other input fields
        }
      }); // Reset all select elements within the modal to their default value

      var selects = modal.querySelectorAll('select');
      selects.forEach(function (select) {
        select.selectedIndex = 0;
      }); // Reset all form switches within the modal to their default state

      var switches = modal.querySelectorAll('.form-check-input[type="checkbox"]');
      switches.forEach(function (switchInput) {
        switchInput.checked = switchInput.defaultChecked; // Reset to default state
      });
    });
  });
});