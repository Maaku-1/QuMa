"use strict";

document.addEventListener('DOMContentLoaded', function () {
  // Get all sidebar pills and responsive sidebar pills
  var sidebarPills = document.querySelectorAll('#sidebar-tabs .nav-link');
  var responsiveSidebarPills = document.querySelectorAll('#responsive-sidebar-tabs .nav-link'); // Function to sync pills

  function syncPills(targetId) {
    // Deactivate all pills
    sidebarPills.forEach(function (pill) {
      pill.classList.remove('active');
      pill.setAttribute('aria-selected', 'false');
    });
    responsiveSidebarPills.forEach(function (pill) {
      pill.classList.remove('active');
      pill.setAttribute('aria-selected', 'false');
    }); // Activate the clicked pill and its counterpart

    var sidebarPill = document.querySelector("#sidebar-tabs .nav-link[data-bs-target=\"".concat(targetId, "\"]"));
    var responsiveSidebarPill = document.querySelector("#responsive-sidebar-tabs .nav-link[data-bs-target=\"".concat(targetId, "\"]"));

    if (sidebarPill) {
      sidebarPill.classList.add('active');
      sidebarPill.setAttribute('aria-selected', 'true');
    }

    if (responsiveSidebarPill) {
      responsiveSidebarPill.classList.add('active');
      responsiveSidebarPill.setAttribute('aria-selected', 'true');
    }
  } // Add click event listeners to sidebar pills


  sidebarPills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      var targetId = this.getAttribute('data-bs-target');
      syncPills(targetId);
    });
  }); // Add click event listeners to responsive sidebar pills

  responsiveSidebarPills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      var targetId = this.getAttribute('data-bs-target');
      syncPills(targetId);
    });
  });
});