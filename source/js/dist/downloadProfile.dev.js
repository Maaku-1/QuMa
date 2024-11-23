"use strict";

document.addEventListener('DOMContentLoaded', function () {
  console.log('DOM fully loaded and parsed');
  var downloadButton = document.getElementById('profile-details-download-profile-btn');
  var userImage = document.getElementById('user-image');
  var placeholderSVG = document.querySelector('.profile-picture-placeholder');

  if (!downloadButton) {
    console.error('Download button not found');
    return;
  } // Fallback to the placeholder if userImage has no src or loading fails


  function checkImageSrc() {
    if (!userImage.src || userImage.src === window.location.href || userImage.src === '') {
      console.warn('User image source is empty or invalid, showing placeholder.');
      userImage.style.display = 'none'; // Hide the img tag if no image is present

      placeholderSVG.style.display = 'block'; // Show the placeholder SVG
    } else {
      userImage.style.display = 'block'; // Ensure image is visible if it has a valid src

      placeholderSVG.style.display = 'none'; // Hide the placeholder
    }
  } // Convert the SVG to a PNG blob


  function svgToPng(svgElement, callback) {
    var svgData = new XMLSerializer().serializeToString(svgElement);
    var canvas = document.createElement('canvas');
    var img = new Image();
    var svgSize = svgElement.getBoundingClientRect();
    canvas.width = svgSize.width;
    canvas.height = svgSize.height;

    img.onload = function () {
      var ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0);
      canvas.toBlob(function (blob) {
        var url = URL.createObjectURL(blob);
        callback(url);
      });
    };

    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
  } // Run the check on DOM load


  checkImageSrc(); // If the image fails to load, fall back to placeholder

  userImage.onerror = function () {
    console.error('Failed to load image, using placeholder.');
    userImage.style.display = 'none'; // Hide the broken image

    placeholderSVG.style.display = 'block'; // Show the placeholder
  }; // Event listener for the download button


  downloadButton.addEventListener('click', function () {
    console.log('Download button clicked');
    var node = document.querySelector('.profile-card-wrapper');

    if (!node) {
      console.error('Profile card wrapper not found');
      return;
    } // Convert the SVG if the placeholder is visible


    if (placeholderSVG.style.display === 'block') {
      svgToPng(placeholderSVG, function (pngUrl) {
        // Temporarily replace the img src with the generated PNG from SVG
        userImage.src = pngUrl;
        userImage.style.display = 'block';
        placeholderSVG.style.display = 'none'; // Generate the image of the profile card

        htmlToImage.toPng(node).then(function (dataUrl) {
          var link = document.createElement('a');
          link.download = 'profile-card.png';
          link.href = dataUrl;
          link.click();
        })["catch"](function (error) {
          console.error('Error generating image:', error);
        }); // After generating the image, restore the placeholder

        userImage.style.display = 'none';
        placeholderSVG.style.display = 'block';
      });
    } else {
      // Normal flow if image is valid
      htmlToImage.toPng(node).then(function (dataUrl) {
        var link = document.createElement('a');
        link.download = 'profile-card.png';
        link.href = dataUrl;
        link.click();
      })["catch"](function (error) {
        console.error('Error generating image:', error);
      });
    }
  });
});