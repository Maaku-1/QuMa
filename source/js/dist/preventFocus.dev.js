"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var sidebarButtons = document.querySelectorAll('.nav-link, .logout-btn');
  sidebarButtons.forEach(function (button) {
    button.addEventListener('keydown', function (event) {
      if (event.key >= '0' && event.key <= '9') {
        event.preventDefault();
        button.blur(); // Remove focus from the button
      }
    });
  });
});