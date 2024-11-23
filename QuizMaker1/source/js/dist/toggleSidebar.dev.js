"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var toggleSidebarBtn = document.getElementById('toggle-sidebar-btn');
  var sidebar = document.getElementById('sidebar');
  var adminDashboardContent = document.querySelector('.admin-dashboard-content');
  var studentDashboardContent = document.querySelector('.student-dashboard-content');

  function toggleFullWidth(contentElement) {
    // Toggle full-width if screen width is greater than 991.99px
    if (window.innerWidth > 991.99) {
      contentElement.classList.toggle('full-width');
    }
  }

  function handleResize() {
    // If screen is 991.98px or smaller, remove full-width
    if (window.innerWidth <= 991.98) {
      if (adminDashboardContent) {
        adminDashboardContent.classList.remove('full-width');
      }

      if (studentDashboardContent) {
        studentDashboardContent.classList.remove('full-width');
      }
    } // If screen is above 991.99px, add full-width back if sidebar is hidden
    else {
        if (adminDashboardContent && sidebar.classList.contains('hidden')) {
          adminDashboardContent.classList.add('full-width');
        }

        if (studentDashboardContent && sidebar.classList.contains('hidden')) {
          studentDashboardContent.classList.add('full-width');
        }
      }
  } // Toggle the sidebar and handle full-width toggle


  toggleSidebarBtn.addEventListener('click', function () {
    sidebar.classList.toggle('hidden');

    if (adminDashboardContent) {
      toggleFullWidth(adminDashboardContent);
    }

    if (studentDashboardContent) {
      toggleFullWidth(studentDashboardContent);
    }
  }); // Add resize event listener to manage class addition/removal on screen resize

  window.addEventListener('resize', handleResize); // Call handleResize on page load to ensure correct layout

  handleResize();
});