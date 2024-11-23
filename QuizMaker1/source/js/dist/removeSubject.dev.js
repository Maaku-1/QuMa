"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var removeSubjectConfirmBtn = document.querySelector('.remove-subject-confirm');
  var selectedCardId = null; // Function to set the selected card ID

  function setSelectedCardId(cardId) {
    selectedCardId = cardId;
  } // Add click event listener to each card's remove button in the more options menu


  document.querySelectorAll('.remove-subject').forEach(function (button) {
    button.addEventListener('click', function () {
      var card = button.closest('.card-template');
      var cardId = card.getAttribute('data-card-id');
      setSelectedCardId(cardId);
    });
  }); // Add click event listener to the confirm button in the remove subject modal

  removeSubjectConfirmBtn.addEventListener('click', function () {
    if (selectedCardId) {
      var cardToRemove = document.querySelector(".card-template[data-card-id=\"".concat(selectedCardId, "\"]"));

      if (cardToRemove) {
        // Remove the card immediately
        cardToRemove.remove(); // Close the modal

        var modal = bootstrap.Modal.getInstance(document.getElementById('remove-subject-modal'));
        modal.hide();
        selectedCardId = null; // Reset the selected card ID
      }
    }
  });
});