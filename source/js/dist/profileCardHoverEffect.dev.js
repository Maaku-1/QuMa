"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var profileCardWrapper = document.querySelector('.profile-card-wrapper');
  var hoverEffect = document.createElement('div');
  hoverEffect.classList.add('hover-effect');
  profileCardWrapper.appendChild(hoverEffect);
  profileCardWrapper.addEventListener('mousemove', function (event) {
    var rect = profileCardWrapper.getBoundingClientRect();
    var x = event.clientX - rect.left;
    var y = event.clientY - rect.top;
    hoverEffect.style.transform = "translate(".concat(x, "px, ").concat(y, "px) scale(1)");
  });
  profileCardWrapper.addEventListener('mouseleave', function () {
    hoverEffect.style.transform = 'translate(-50%, -50%) scale(0)';
  });
});