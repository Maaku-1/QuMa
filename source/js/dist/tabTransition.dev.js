"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var tabLinks = document.querySelectorAll('.sidebar-pills');
  var animationEl = document.createElement('div');
  animationEl.id = 'pills-animation';
  document.querySelector('.nav').appendChild(animationEl);

  function updateAnimation(el) {
    var offsetTop = el.offsetTop;
    var height = el.offsetHeight;
    animationEl.style.height = "".concat(height, "px");
    animationEl.style.top = "".concat(offsetTop, "px");
  }

  tabLinks.forEach(function (tabLink) {
    if (tabLink.id === 'logout-tab') return;
    tabLink.addEventListener('click', function () {
      tabLinks.forEach(function (link) {
        link.classList.remove('active');
        link.setAttribute('aria-selected', 'false');
      });
      tabLink.classList.add('active');
      tabLink.setAttribute('aria-selected', 'true');
      updateAnimation(tabLink);
      var targetPane = document.querySelector(tabLink.getAttribute('data-bs-target'));
      document.querySelectorAll('.tab-pane').forEach(function (pane) {
        pane.classList.remove('show', 'active');
      });
      targetPane.classList.add('show', 'active');
    });
  }); // Initialize animation position

  updateAnimation(document.querySelector('.sidebar-pills.active'));
});