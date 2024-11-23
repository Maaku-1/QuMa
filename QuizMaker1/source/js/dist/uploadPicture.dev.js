"use strict";

document.getElementById('upload-image').addEventListener('change', function (event) {
  var file = event.target.files[0];

  if (file) {
    var reader = new FileReader();

    reader.onload = function (e) {
      var profileImage = document.getElementById('user-image');
      profileImage.src = e.target.result;
      profileImage.style.display = 'block';
      document.getElementById('placeholder-icon').style.display = 'none';
    };

    reader.readAsDataURL(file);
  }
});