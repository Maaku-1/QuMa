"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var passwordInputs = document.querySelectorAll('.settings-change-password-field');
  var pillLinks = document.querySelectorAll('.nav-pills .nav-link');
  passwordInputs.forEach(function (input) {
    var visibilityIcon = input.nextElementSibling.nextElementSibling;
    var visibilityOffIcon = visibilityIcon.nextElementSibling; // Function to toggle visibility

    var toggleVisibility = function toggleVisibility() {
      if (input.type === 'password') {
        input.type = 'text';
        visibilityIcon.style.display = 'none';
        visibilityOffIcon.style.display = 'block';
      } else {
        input.type = 'password';
        visibilityIcon.style.display = 'block';
        visibilityOffIcon.style.display = 'none';
      }
    }; // Initialize visibility icons based on input value


    var initializeVisibilityIcons = function initializeVisibilityIcons() {
      if (input.value.length > 0) {
        if (input.type === 'password') {
          visibilityIcon.style.display = 'block';
          visibilityOffIcon.style.display = 'none';
        } else {
          visibilityIcon.style.display = 'none';
          visibilityOffIcon.style.display = 'block';
        }
      } else {
        visibilityIcon.style.display = 'none';
        visibilityOffIcon.style.display = 'none';
        input.type = 'password'; // Reset to hidden state when input is cleared
      }
    }; // Initialize icons on page load


    initializeVisibilityIcons();
    input.addEventListener('input', function () {
      initializeVisibilityIcons();
    });
    input.addEventListener('focus', function () {
      if (input.value.length > 0) {
        if (input.type === 'password') {
          visibilityIcon.style.display = 'block';
          visibilityOffIcon.style.display = 'none';
        } else {
          visibilityIcon.style.display = 'none';
          visibilityOffIcon.style.display = 'block';
        }
      }
    });
    input.addEventListener('blur', function () {
      visibilityIcon.style.display = 'none';
      visibilityOffIcon.style.display = 'none';
    });
    visibilityIcon.addEventListener('mousedown', function (event) {
      event.preventDefault(); // Prevent default behavior

      toggleVisibility();
    });
    visibilityOffIcon.addEventListener('mousedown', function (event) {
      event.preventDefault(); // Prevent default behavior

      toggleVisibility();
    }); // Ensure icons are always clickable even when the input is long or edited

    input.addEventListener('input', function () {
      visibilityIcon.style.pointerEvents = 'auto';
      visibilityOffIcon.style.pointerEvents = 'auto';
    });
  }); // Add event listener to pill navigation elements

  pillLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      console.log('Pill navigation element clicked');
      var currentPillForm = document.querySelector('.tab-pane.active form');
      currentPillForm.reset();
    });
  });
});