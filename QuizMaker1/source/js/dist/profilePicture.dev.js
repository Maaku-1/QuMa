"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var uploadImageInput = document.getElementById('upload-image');
  var userImage = document.getElementById('user-image');
  var sidebarUserImage = document.querySelector('.sidebar-profile-picture-user-image');
  var responsiveSidebarUserImage = document.querySelector('.responsive-sidebar-profile-picture-user-image');
  var sidebarPlaceholder = document.querySelector('.sidebar-profile-picture-placeholder');
  var responsiveSidebarPlaceholder = document.querySelector('.responsive-sidebar-profile-picture-placeholder');
  var profilePlaceholder = document.querySelector('.profile-picture-placeholder');
  uploadImageInput.addEventListener('change', function (event) {
    var file = event.target.files[0];

    if (file) {
      var reader = new FileReader();

      reader.onload = function (e) {
        var imageUrl = e.target.result;
        userImage.src = imageUrl;
        userImage.style.display = 'block';
        userImage.style.objectFit = 'cover'; // Ensure the image covers the container
        // Sync with sidebar profile picture

        sidebarUserImage.src = imageUrl;
        sidebarUserImage.style.display = 'block';
        sidebarUserImage.style.objectFit = 'cover'; // Ensure the image covers the container

        sidebarPlaceholder.style.display = 'none'; // Hide the sidebar placeholder
        // Sync with responsive sidebar profile picture

        responsiveSidebarUserImage.src = imageUrl;
        responsiveSidebarUserImage.style.display = 'block';
        responsiveSidebarUserImage.style.objectFit = 'cover'; // Ensure the image covers the container

        responsiveSidebarPlaceholder.style.display = 'none'; // Hide the responsive sidebar placeholder

        profilePlaceholder.style.display = 'none'; // Hide the profile placeholder
      };

      reader.readAsDataURL(file);
    }
  });
});