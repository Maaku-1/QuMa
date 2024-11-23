"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var header = document.querySelector('.header-wrapper');
  var stickyClass = 'is-sticky';

  var handleScroll = function handleScroll() {
    if (window.scrollY > 0) {
      header.classList.add(stickyClass);
    } else {
      header.classList.remove(stickyClass);
    }
  };

  window.addEventListener('scroll', handleScroll);
  handleScroll(); // Initial check in case the page is already scrolled
});