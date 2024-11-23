"use strict";

function addChoice() {
  var container = document.getElementById('choice_container');
  var index = container.querySelectorAll('input[name="choices[]"]').length; // Current count of choices

  if (index < 26) {
    // Limit to 26 choices
    var div = document.createElement('div');
    div.className = 'choice';
    div.innerHTML = "\n            <input type=\"text\" name=\"choices[]\" required>\n            <label>\n                <input type=\"checkbox\" name=\"correct_choices[]\" style=\"margin-left: 10px;\">\n                Correct\n            </label>\n            <button type=\"button\" onclick=\"removeChoice(this)\">Delete</button>\n            <br>\n        ";
    container.appendChild(div);
  } else {
    alert('You can only add up to 26 choices.');
  }
}