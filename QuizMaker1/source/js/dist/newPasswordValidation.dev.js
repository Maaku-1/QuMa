"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var newPasswordInput = document.getElementById('new-password');
  var confirmPasswordInput = document.getElementById('confirm-password');

  function validatePasswords() {
    if (newPasswordInput.value === '' && confirmPasswordInput.value === '') {
      newPasswordInput.classList.remove('is-valid', 'is-invalid');
      confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
    } else if (newPasswordInput.value === confirmPasswordInput.value) {
      newPasswordInput.classList.remove('is-invalid');
      confirmPasswordInput.classList.remove('is-invalid');
      newPasswordInput.classList.add('is-valid');
      confirmPasswordInput.classList.add('is-valid');
    } else {
      newPasswordInput.classList.remove('is-valid');
      confirmPasswordInput.classList.remove('is-valid');
      newPasswordInput.classList.add('is-invalid');
      confirmPasswordInput.classList.add('is-invalid');
    }
  }

  newPasswordInput.addEventListener('input', validatePasswords);
  confirmPasswordInput.addEventListener('input', validatePasswords);
});