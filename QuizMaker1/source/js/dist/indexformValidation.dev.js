"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('.login-form');
  var schIdInput = document.getElementById('sch-id');
  var pwInput = document.getElementById('pw');
  form.addEventListener('submit', function (event) {
    // Prevent form submission for custom validation
    event.preventDefault(); // Reset validation styles

    form.classList.remove('was-validated'); // Check if the form is valid using Bootstrap validation

    if (!form.checkValidity()) {
      event.stopPropagation();
      form.classList.add('was-validated'); // Bootstrap class for showing validation feedback

      return;
    } // Client-side validation for School ID (numeric and <= 25 digits)


    var schIdValue = schIdInput.value.trim();

    if (!/^\d{1,25}$/.test(schIdValue)) {
      schIdInput.setCustomValidity('Invalid');
      schIdInput.nextElementSibling.textContent = 'School ID should be numeric and up to 25 digits.';
    } else {
      schIdInput.setCustomValidity('');
    } // Client-side validation for password (check if not empty)


    var pwValue = pwInput.value.trim();

    if (pwValue.length === 0) {
      pwInput.setCustomValidity('Invalid');
      pwInput.nextElementSibling.textContent = 'Password is required.';
    } else {
      pwInput.setCustomValidity('');
    } // Revalidate form after custom checks


    if (!form.checkValidity()) {
      event.stopPropagation();
      form.classList.add('was-validated');
      return;
    } // If client-side validation passes, proceed to server-side validation


    serverSideValidation(schIdValue, pwValue);
  }); // Function to handle server-side validation via AJAX

  function serverSideValidation(schId, password) {
    // Mock AJAX request to server for validation
    fetch('/api/validate-login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        schId: schId,
        password: password
      })
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      if (data.success) {
        // Successful login
        window.location.href = '/dashboard'; // Redirect to dashboard
      } else {
        // Display server-side validation errors
        if (data.errors.schId) {
          schIdInput.setCustomValidity('Invalid');
          schIdInput.nextElementSibling.textContent = data.errors.schId;
          schIdInput.classList.add('is-invalid');
        } else {
          schIdInput.setCustomValidity('');
        }

        if (data.errors.password) {
          pwInput.setCustomValidity('Invalid');
          pwInput.nextElementSibling.textContent = data.errors.password;
          pwInput.classList.add('is-invalid');
        } else {
          pwInput.setCustomValidity('');
        } // Add was-validated class to trigger Bootstrap tooltips


        form.classList.add('was-validated');
      }
    })["catch"](function (error) {
      console.error('Error during server-side validation:', error);
    });
  }
});