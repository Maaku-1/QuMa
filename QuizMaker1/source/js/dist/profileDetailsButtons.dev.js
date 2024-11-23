"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var htmlToImage = _interopRequireWildcard(require("html-to-image"));

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

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
  var downloadButton = document.querySelector('.profile-details-download-profile-btn');
  var profileCard = document.querySelector('.profile-card-wrapper');
  var profileCardHeader = document.querySelector('.profile-card-header');
  var profileCardBody = document.querySelector('.profile-card-body');
  var profileCardDetails = document.querySelector('.profile-card-details');
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
  downloadButton.addEventListener('click', function () {
    // Apply temporary styles for the screenshot
    profileCard.style.setProperty('border-radius', '15px', 'important');
    profileCard.style.setProperty('background-color', 'transparent', 'important');
    profileCardHeader.style.setProperty('border-radius', '15px 15px 0 0', 'important');
    profileCardBody.style.setProperty('border-radius', '0 0 15px 15px', 'important');
    htmlToImage.toPng(profileCard).then(function (dataUrl) {
      // Generate unique file name
      var now = new Date();
      var date = now.toISOString().split('T')[0]; // YYYY-MM-DD

      var time = now.toTimeString().split(' ')[0].replace(/:/g, '-'); // HH-MM-SS

      var firstName = firstNameInput.value;
      var middleName = middleNameInput.value ? middleNameInput.value.charAt(0) + '.' : '';
      var lastName = lastNameInput.value;
      var fullName = "".concat(firstName, " ").concat(middleName, " ").concat(lastName).trim().replace(/\s+/g, '_'); // Replace spaces with underscores

      var fileName = fullName ? "".concat(date, ".").concat(time, ".").concat(fullName, ".png") : "".concat(date, ".").concat(time, ".png");
      var link = document.createElement('a');
      link.href = dataUrl;
      link.download = fileName; // Append the link to the body to ensure it works on mobile devices

      document.body.appendChild(link);
      link.click(); // Remove the link after download

      document.body.removeChild(link); // Reset temporary styles after a short delay to ensure the download is initiated

      setTimeout(function () {
        profileCard.style.removeProperty('border-radius');
        profileCard.style.removeProperty('background-color');
        profileCardHeader.style.removeProperty('border-radius');
        profileCardBody.style.removeProperty('border-radius');
      }, 100); // 100ms delay
    });
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