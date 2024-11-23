"use strict";

document.addEventListener('DOMContentLoaded', function () {
  // Function to clear all input fields in a modal
  var clearModalFields = function clearModalFields(modalId) {
    var modal = document.getElementById(modalId);

    if (modal) {
      // Clear all input fields
      var inputs = modal.querySelectorAll('input');
      inputs.forEach(function (input) {
        if (input.type === 'radio' || input.type === 'checkbox') {
          input.checked = false;
        } else {
          input.value = '';
        }
      }); // Reset all select elements

      var selects = modal.querySelectorAll('select');
      selects.forEach(function (select) {
        select.selectedIndex = 0;
      }); // Clear all textarea fields

      var textareas = modal.querySelectorAll('textarea');
      textareas.forEach(function (textarea) {
        textarea.value = '';
      });
    }
  }; // Event listener for 'Create Subject' modal close


  var createSubjectModal = document.getElementById('create-subject-modal');

  if (createSubjectModal) {
    createSubjectModal.addEventListener('hidden.bs.modal', function () {
      clearModalFields('create-subject-modal');
    });
  } // Event listener for 'Join Subject' modal close


  var joinSubjectModal = document.getElementById('join-subject-modal');

  if (joinSubjectModal) {
    joinSubjectModal.addEventListener('hidden.bs.modal', function () {
      clearModalFields('join-subject-modal');
    });
  }
});