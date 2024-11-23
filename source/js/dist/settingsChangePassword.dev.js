"use strict";

document.getElementById('settings-change-password-confirm').addEventListener('click', function () {
  var form = document.getElementById('settings-change-password-form');
  var newPasswordInput = document.getElementById('new-password');
  var confirmPasswordInput = document.getElementById('confirm-password'); // Validate passwords

  if (newPasswordInput.value === '' || confirmPasswordInput.value === '' || newPasswordInput.value !== confirmPasswordInput.value) {
    // Show validation feedback
    if (newPasswordInput.value === '' || confirmPasswordInput.value === '') {
      newPasswordInput.classList.remove('is-valid');
      confirmPasswordInput.classList.remove('is-valid');
      newPasswordInput.classList.add('is-invalid');
      confirmPasswordInput.classList.add('is-invalid');
    } else if (newPasswordInput.value !== confirmPasswordInput.value) {
      newPasswordInput.classList.remove('is-valid');
      confirmPasswordInput.classList.remove('is-valid');
      newPasswordInput.classList.add('is-invalid');
      confirmPasswordInput.classList.add('is-invalid');
    }
  } else {
    // Submit the form if valid
    form.submit();
  }
});