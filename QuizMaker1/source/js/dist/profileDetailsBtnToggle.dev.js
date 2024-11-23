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
  var sidebarName = document.getElementById('name');
  var profileCardName = document.querySelector('.profile-card-name');
  var downloadButton = document.querySelector('.profile-details-download-profile-btn');
  var profileCard = document.querySelector('.profile-card-wrapper');
  var profileCardHeader = document.querySelector('.profile-card-header');
  var profileCardBody = document.querySelector('.profile-card-body');
  var mainProfileWrapper = document.querySelector('.main-profile-wrapper');
  var initialFirstName, initialMiddleName, initialLastName, initialCampus, initialDepartment;
  editButton.addEventListener('click', function () {
    editButton.style.display = 'none';
    cancelButton.style.display = 'inline-block';
    confirmButton.style.display = 'inline-block'; // Store initial values

    initialFirstName = firstNameInput.value;
    initialMiddleName = middleNameInput.value;
    initialLastName = lastNameInput.value;
    initialCampus = campusInput.value;
    initialDepartment = departmentInput.value; // Make input fields editable

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

    firstNameInput.value = initialFirstName;
    middleNameInput.value = initialMiddleName;
    lastNameInput.value = initialLastName;
    campusInput.value = initialCampus;
    departmentInput.value = initialDepartment; // Make input fields read-only again

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
    var formattedName = "".concat(firstName, " ").concat(middleName, " ").concat(lastName); // Update sidebar profile details

    sidebarName.textContent = formattedName; // Update profile card details

    profileCardName.textContent = formattedName;
  });
  downloadButton.addEventListener('click', function () {
    // Apply temporary styles for the screenshot
    profileCard.style.setProperty('border-radius', '15px', 'important');
    profileCard.style.setProperty('background-color', 'transparent', 'important');
    profileCardHeader.style.setProperty('border-radius', '15px 15px 0 0', 'important');
    profileCardBody.style.setProperty('border-radius', '0 0 15px 15px', 'important');
    mainProfileWrapper.style.setProperty('gap', '30px', 'important');
    html2canvas(profileCard, {
      backgroundColor: null
    }).then(function (canvas) {
      // Generate unique file name
      var now = new Date();
      var date = now.toISOString().split('T')[0]; // YYYY-MM-DD

      var time = now.toTimeString().split(' ')[0].replace(/:/g, '-'); // HH-MM-SS

      var firstName = firstNameInput.value;
      var middleName = middleNameInput.value ? middleNameInput.value.charAt(0) + '.' : '';
      var lastName = lastNameInput.value;
      var fullName = "".concat(firstName, " ").concat(middleName, " ").concat(lastName).trim().replace(/\s+/g, '_'); // Replace spaces with underscores

      var fileName = "".concat(date, ".").concat(time, ".").concat(fullName, ".png");
      var link = document.createElement('a');
      link.href = canvas.toDataURL('image/png');
      link.download = fileName;
      link.click(); // Revert styles back to original

      profileCard.style.removeProperty('border-radius');
      profileCard.style.removeProperty('background-color');
      profileCardHeader.style.removeProperty('border-radius');
      profileCardBody.style.removeProperty('border-radius');
      mainProfileWrapper.style.removeProperty('gap');
    });
  });
});