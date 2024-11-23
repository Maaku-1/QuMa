"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var createSubjectForm = document.getElementById('create-subject-form');
  var mainSubjectsWrapper = document.querySelector('.main-subjects-wrapper');
  var cardCounter = 1; // Counter to ensure unique IDs

  createSubjectForm.addEventListener('submit', function (event) {
    event.preventDefault();
    var subjectName = document.getElementById('create-subject-name').value;
    var subjectProgram = document.getElementById('create-subject-program').value;
    var subjectYear = document.getElementById('create-subject-year').value;
    var subjectSection = document.getElementById('create-subject-section').value; // Generate a unique ID using timestamp and counter

    var uniqueId = "card-".concat(Date.now(), "-").concat(cardCounter);
    cardCounter++;
    var newCard = document.createElement('div');
    newCard.classList.add('card', 'card-template');
    newCard.setAttribute('data-card-id', uniqueId);
    newCard.innerHTML = "\n            <div class=\"card-header\">\n                <div class=\"card-details\">\n                    <span class=\"card-subject-name font-inria-sans-bold\">".concat(subjectName, "</span>\n                    <span class=\"card-subject-program font-rubik\">").concat(subjectProgram, "</span>\n                    <span class=\"card-subject-year-section font-rubik\">").concat(subjectYear, "-").concat(subjectSection, "</span>\n                </div>\n                <div class=\"more-options-wrapper\">\n                    <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"more-options-btn\" type=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\" viewBox=\"0 -960 960 960\">\n                        <path d=\"M480-160q-33 0-56.5-23.5T400-240q0-33 23.5-56.5T480-320q33 0 56.5 23.5T560-240q0 33-23.5 56.5T480-160Zm0-240q-33 0-56.5-23.5T400-480q0-33 23.5-56.5T480-560q33 0 56.5 23.5T560-480q0 33-23.5 56.5T480-400Zm0-240q-33 0-56.5-23.5T400-720q0-33 23.5-56.5T480-800q33 0 56.5 23.5T560-720q0 33-23.5 56.5T480-640Z\"/>\n                    </svg>\n                    <div class=\"dropdown-menu remove-subject-menu-wrapper\">\n                        <div class=\"remove-subject-menu\">\n                            <button type=\"button\" class=\"remove-subject font-poppins-regular\" data-bs-toggle=\"modal\" data-bs-target=\"#remove-subject-modal\">\n                                Remove Subject\n                            </button>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"card-body\"></div>\n            <div class=\"card-footer\"></div>\n        ");
    mainSubjectsWrapper.appendChild(newCard); // Reset the form

    createSubjectForm.reset(); // Close the modal

    var modal = bootstrap.Modal.getInstance(document.getElementById('create-subject-modal'));
    modal.hide(); // Re-attach event listeners for the new remove buttons

    attachRemoveEventListeners();
  });

  function attachRemoveEventListeners() {
    document.querySelectorAll('.remove-subject').forEach(function (button) {
      button.addEventListener('click', function () {
        var card = button.closest('.card-template');
        var cardId = card.getAttribute('data-card-id');
        setSelectedCardId(cardId);
      });
    });
  } // Initial attachment of event listeners


  attachRemoveEventListeners();
});