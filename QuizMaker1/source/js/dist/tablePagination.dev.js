"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var rowsPerPage = 10; // Number of rows to display per page

  var maxPageLinks = 3; // Maximum number of page links to display at a time

  var table = document.querySelector('.quiz-table');
  var rows = Array.from(table.querySelectorAll('.quiz-table-body-row-template'));
  var paginationContainer = document.getElementById('pagination-controls');

  function displayRows(page) {
    var start = (page - 1) * rowsPerPage;
    var end = start + rowsPerPage;
    rows.forEach(function (row, index) {
      row.style.display = index >= start && index < end ? 'grid' : 'none';
    });
  }

  function createPagination(totalRows) {
    var totalPages = Math.ceil(totalRows / rowsPerPage);
    paginationContainer.innerHTML = '';
    var firstPageItem = document.createElement('li');
    firstPageItem.classList.add('page-item');
    firstPageItem.innerHTML = '<a class="page-link" href="#"><span aria-hidden="true">&laquo;</span></a>';
    firstPageItem.addEventListener('click', function (event) {
      event.preventDefault();

      if (!firstPageItem.classList.contains('disabled')) {
        displayRows(1);
        updatePagination(1, totalPages);
      }
    });
    paginationContainer.appendChild(firstPageItem);
    var lastPageItem = document.createElement('li');
    lastPageItem.classList.add('page-item');
    lastPageItem.innerHTML = '<a class="page-link" href="#"><span aria-hidden="true">&raquo;</span></a>';
    lastPageItem.addEventListener('click', function (event) {
      event.preventDefault();

      if (!lastPageItem.classList.contains('disabled')) {
        displayRows(totalPages);
        updatePagination(totalPages, totalPages);
      }
    });
    paginationContainer.appendChild(lastPageItem);
    updatePagination(1, totalPages);
  }

  function updatePagination(currentPage, totalPages) {
    var paginationContainer = document.getElementById('pagination-controls');
    var firstPageItem = paginationContainer.firstChild;
    var lastPageItem = paginationContainer.lastChild; // Remove existing page links

    while (paginationContainer.children.length > 2) {
      paginationContainer.removeChild(paginationContainer.children[1]);
    }

    var startPage = Math.max(1, currentPage - Math.floor(maxPageLinks / 2));
    var endPage = Math.min(totalPages, startPage + maxPageLinks - 1);

    var _loop = function _loop(i) {
      var pageItem = document.createElement('li');
      pageItem.classList.add('page-item');
      pageItem.innerHTML = "<a class=\"page-link\" href=\"#\">".concat(i, "</a>");
      pageItem.addEventListener('click', function (event) {
        event.preventDefault();
        displayRows(i);
        document.querySelectorAll('.page-item').forEach(function (item) {
          return item.classList.remove('active');
        });
        pageItem.classList.add('active');
        updatePagination(i, totalPages);
      });
      paginationContainer.insertBefore(pageItem, lastPageItem);
    };

    for (var i = startPage; i <= endPage; i++) {
      _loop(i);
    } // Set the current page as active


    document.querySelectorAll('.page-item').forEach(function (item) {
      return item.classList.remove('active');
    });
    paginationContainer.children[currentPage - startPage + 1].classList.add('active'); // Disable the first and last page items if necessary

    if (currentPage === 1) {
      firstPageItem.classList.add('disabled');
    } else {
      firstPageItem.classList.remove('disabled');
    }

    if (currentPage === totalPages) {
      lastPageItem.classList.add('disabled');
    } else {
      lastPageItem.classList.remove('disabled');
    }
  } // Initialize pagination


  createPagination(rows.length);
  displayRows(1);
});