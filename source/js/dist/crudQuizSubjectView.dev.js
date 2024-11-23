"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var modals = document.querySelectorAll('.modal'); // Function to add a new quiz

  function addQuiz(quizName, startDate, endDate, timeLimit, showAnswers, randomize) {
    var table = document.getElementById('quiz-table').querySelector('tbody');
    var newRow = document.createElement('tr');
    newRow.classList.add('table-item-template');
    newRow.innerHTML = "\n            <td class=\"font-rubik\"><a href=\"#\">".concat(quizName, "</a></td>\n            <td class=\"font-rubik\">").concat(startDate, "</td>\n            <td class=\"font-rubik\">").concat(endDate, "</td>\n            <td class=\"font-rubik\">").concat(timeLimit, "</td>\n            <td class=\"font-rubik\">").concat(showAnswers ? 'Yes' : 'No', "</td>\n            <td class=\"font-rubik\">").concat(randomize ? 'Yes' : 'No', "</td>\n            <td>\n                <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"table-options-icon\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\" viewBox=\"0 -960 960 960\">\n                    <path d=\"M480-160q-33 0-56.5-23.5T400-240q0-33 23.5-56.5T480-320q33 0 56.5 23.5T560-240q0 33-23.5 56.5T480-160Zm0-240q-33 0-56.5-23.5T400-480q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480q0 33-23.5 56.5T480-400Zm0-240q-33 0-56.5-23.5T400-720q0-33 23.5-56.5T480-800q33 0 56.5 23.5T560-720q0 33-23.5 56.5T480-640Z\"/>\n                </svg>\n                <ul class=\"dropdown-menu\">\n                    <div class=\"quiz-menu\">\n                        <button type=\"button\" class=\"edit-quiz font-poppins-regular\" data-bs-toggle=\"modal\" data-bs-target=\"#edit-quiz-modal\">\n                            Edit Quiz\n                        </button>\n                        <button type=\"button\" class=\"remove-quiz font-poppins-regular\" data-bs-toggle=\"modal\" data-bs-target=\"#remove-quiz-modal\">\n                            Remove Quiz\n                        </button>\n                    </div>\n                </ul>\n            </td>\n        ");
    table.appendChild(newRow);
  } // Function to sync user input with edit quiz modal


  function syncEditQuiz(quizName, startDate, endDate, timeLimit, showAnswers, randomize) {
    document.getElementById('edit-quiz-name').value = quizName;
    document.getElementById('edit-quiz-start-date').value = startDate;
    document.getElementById('edit-quiz-end-date').value = endDate;
    document.getElementById('edit-quiz-time-limit').value = timeLimit;
    document.getElementById('edit-quiz-show-answers').checked = showAnswers;
    document.getElementById('edit-quiz-random').checked = randomize;
  } // Function to remove the selected quiz


  function removeQuiz(row) {
    row.remove();
  } // Event listener for adding a quiz


  document.querySelector('.create-quiz-confirm').addEventListener('click', function () {
    var quizName = document.getElementById('create-quiz-name').value;
    var startDate = document.getElementById('create-quiz-start-date').value.replace('T', ' ');
    var endDate = document.getElementById('create-quiz-end-date').value.replace('T', ' ');
    var timeLimit = document.getElementById('create-quiz-time-limit').value;
    var showAnswers = document.getElementById('create-quiz-show-answers').checked;
    var randomize = document.getElementById('create-quiz-random').checked;
    addQuiz(quizName, startDate, endDate, timeLimit, showAnswers, randomize);
    syncEditQuiz(quizName, startDate, endDate, timeLimit, showAnswers, randomize); // Clear the form fields

    document.getElementById('create-quiz-form').reset();
  }); // Event listener for syncing edit quiz modal

  document.getElementById('quiz-table').addEventListener('click', function (event) {
    if (event.target.classList.contains('edit-quiz')) {
      var row = event.target.closest('tr');
      var quizName = row.querySelector('td:nth-child(1) a').textContent;
      var startDate = row.querySelector('td:nth-child(2)').textContent;
      var endDate = row.querySelector('td:nth-child(3)').textContent;
      var timeLimit = row.querySelector('td:nth-child(4)').textContent;
      var showAnswers = row.querySelector('td:nth-child(5)').textContent === 'Yes';
      var randomize = row.querySelector('td:nth-child(6)').textContent === 'Yes';
      syncEditQuiz(quizName, startDate, endDate, timeLimit, showAnswers, randomize);
    }
  }); // Event listener for removing a quiz

  document.getElementById('quiz-table').addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-quiz')) {
      var row = event.target.closest('tr');
      var confirmButton = document.querySelector('.remove-quiz-confirm');
      confirmButton.addEventListener('click', function () {
        removeQuiz(row);
      }, {
        once: true
      });
    }
  }); // Clear modal fields when hidden

  modals.forEach(function (modal) {
    modal.addEventListener('hidden.bs.modal', function () {
      // Clear all input fields within the modal
      var inputs = modal.querySelectorAll('input');
      inputs.forEach(function (input) {
        if (input.id === 'create-quiz-start-date' || input.id === 'create-quiz-end-date') {
          input.value = '2024-01-01 12:00'; // Reset to default value
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