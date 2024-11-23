"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var editButton = document.getElementById('edit-profile-btn');
  var cancelButton = document.getElementById('cancel-edit-profile-btn');
  var confirmButton = document.getElementById('confirm-edit-profile-btn');
  var firstNameInput = document.getElementById('first-name');
  var middleNameInput = document.getElementById('middle-name');
  var lastNameInput = document.getElementById('last-name');
  var campusInput = document.getElementById('campus');
  var departmentInput = document.getElementById('department');
  var sidebarName = document.querySelector('.sidebar-profile-name');
  var responsiveName = document.querySelector('.responsive-sidebar-profile-name');
  var profileCardName = document.querySelector('.profile-card-name');
  var tabButtons = document.querySelectorAll('.nav-link');
  var initialFirstName, initialMiddleName, initialLastName, initialCampus, initialDepartment;
  var lastConfirmedFirstName, lastConfirmedMiddleName, lastConfirmedLastName, lastConfirmedCampus, lastConfirmedDepartment;

  function storeInitialValues() {
    initialFirstName = firstNameInput.value;
    initialMiddleName = middleNameInput.value;
    initialLastName = lastNameInput.value;
    initialCampus = campusInput.value;
    initialDepartment = departmentInput.value;
  }

  function storeConfirmedValues() {
    lastConfirmedFirstName = firstNameInput.value;
    lastConfirmedMiddleName = middleNameInput.value;
    lastConfirmedLastName = lastNameInput.value;
    lastConfirmedCampus = campusInput.value;
    lastConfirmedDepartment = campusInput.value;
  }

  function revertToInitialValues() {
    firstNameInput.value = initialFirstName;
    middleNameInput.value = initialMiddleName;
    lastNameInput.value = initialLastName;
    campusInput.value = initialCampus;
    departmentInput.value = initialDepartment;
  }

  function revertToConfirmedValues() {
    firstNameInput.value = lastConfirmedFirstName;
    middleNameInput.value = lastConfirmedMiddleName;
    lastNameInput.value = lastConfirmedLastName;
    campusInput.value = lastConfirmedCampus;
    departmentInput.value = lastConfirmedDepartment;
  }

  editButton.addEventListener('click', function () {
    editButton.style.display = 'none';
    cancelButton.style.display = 'inline-block';
    confirmButton.style.display = 'inline-block'; // Store initial values

    storeInitialValues(); // Make input fields editable

    firstNameInput.readOnly = false;
    middleNameInput.readOnly = false;
    lastNameInput.readOnly = false;
    campusInput.readOnly = false;
    departmentInput.readOnly = false;
  });
  cancelButton.addEventListener('click', function () {
    editButton.style.display = 'inline-block';
    cancelButton.style.display = 'none';
    confirmButton.style.display = 'none'; // Revert to initial values

    revertToInitialValues(); // Make input fields read-only again

    firstNameInput.readOnly = true;
    middleNameInput.readOnly = true;
    lastNameInput.readOnly = true;
    campusInput.readOnly = true;
    departmentInput.readOnly = true;
  });
  confirmButton.addEventListener('click', function () {
    editButton.style.display = 'inline-block';
    cancelButton.style.display = 'none';
    confirmButton.style.display = 'none'; // Make input fields read-only again

    firstNameInput.readOnly = true;
    middleNameInput.readOnly = true;
    lastNameInput.readOnly = true;
    campusInput.readOnly = true;
    departmentInput.readOnly = true; // Format the name

    var firstName = firstNameInput.value;
    var middleName = middleNameInput.value ? middleNameInput.value.charAt(0) + '.' : '';
    var lastName = lastNameInput.value;
    var formattedName = "".concat(firstName, " ").concat(middleName, " ").concat(lastName);
    var firstWordOfFirstName = firstName.split(' ')[0];
    var sidebarFormattedName = "".concat(firstWordOfFirstName, " ").concat(lastName); // Update sidebar profile details with only first and last name

    sidebarName.textContent = sidebarFormattedName;
    responsiveName.textContent = sidebarFormattedName; // Update profile card details with full name

    profileCardName.textContent = formattedName; // Store confirmed values

    storeConfirmedValues();
  });
  tabButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      if (editButton.style.display === 'none') {
        // Revert to last confirmed values if there are unsaved changes
        revertToConfirmedValues(); // Make input fields read-only again

        firstNameInput.readOnly = true;
        middleNameInput.readOnly = true;
        lastNameInput.readOnly = true;
        campusInput.readOnly = true;
        departmentInput.readOnly = true; // Reset buttons

        editButton.style.display = 'inline-block';
        cancelButton.style.display = 'none';
        confirmButton.style.display = 'none';
      }
    });
  }); // Store initial confirmed values

  storeConfirmedValues();
});