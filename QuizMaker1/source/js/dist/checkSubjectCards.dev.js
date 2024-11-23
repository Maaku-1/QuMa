"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var mainSubjectsWrapper = document.querySelector('.main-subjects-wrapper');
  var noItemWrapper = document.querySelector('.main-subjects-no-item-wrapper');

  function checkSubjectCards() {
    var subjectCards = mainSubjectsWrapper.querySelectorAll('.card-template');

    if (subjectCards.length === 0) {
      noItemWrapper.style.display = 'flex';
      mainSubjectsWrapper.style.minHeight = '300px'; // Ensure minimum height to center no-item-wrapper
    } else {
      noItemWrapper.style.display = 'none';
      mainSubjectsWrapper.style.minHeight = 'auto'; // Reset min-height when there are subject cards
    }
  } // Initial check


  checkSubjectCards();
});