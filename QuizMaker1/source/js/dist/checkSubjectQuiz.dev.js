"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var quizTableBody = document.querySelector('.quiz-table-body');
  var noItemWrapper = document.querySelector('.main-quiz-no-item-wrapper');
  var quizTableHeader = document.querySelector('.quiz-table-header');
  var paginationNav = document.querySelector('nav');

  function checkQuizItems() {
    var quizItems = quizTableBody.querySelectorAll('.quiz-table-body-row-template');

    if (quizItems.length === 0) {
      noItemWrapper.style.display = 'flex';
      quizTableBody.style.minHeight = '300px'; // Ensure minimum height to center no-item-wrapper

      quizTableHeader.style.display = 'none';
      paginationNav.style.display = 'none'; // Hide pagination when there are no quiz items
    } else {
      noItemWrapper.style.display = 'none';
      quizTableBody.style.minHeight = 'auto'; // Reset min-height when there are quiz items

      quizTableHeader.style.display = 'grid';
      paginationNav.style.display = 'flex'; // Show pagination when there are quiz items
    }
  } // Initial check


  checkQuizItems();
});