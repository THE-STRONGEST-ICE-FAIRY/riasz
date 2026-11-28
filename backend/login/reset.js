/* Small enhancements for reset.php. The server re-validates everything. */
(function () {
  'use strict';

  var form = document.getElementById('reset-form');
  if (!form) return;

  var pw = document.getElementById('password');
  var confirmPw = document.getElementById('confirm_password');
  var matchError = document.getElementById('match-error');
  var minLength = parseInt(pw.getAttribute('minlength'), 10) || 12;

  // Give each password field its own show / hide control.
  form.querySelectorAll('.toggle-password').forEach(function (toggle) {
    var input = document.getElementById(toggle.dataset.target);
    if (!input) return;

    toggle.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      toggle.querySelector('img').src = show ? toggle.dataset.srcShow : toggle.dataset.srcHide;
      toggle.setAttribute('aria-label', show ? toggle.dataset.labelHide : toggle.dataset.labelShow);
    });
  });

  function checkMatch() {
    matchError.textContent =
      confirmPw.value && pw.value !== confirmPw.value ? 'Passwords do not match.' : '';
  }

  pw.addEventListener('input', checkMatch);
  confirmPw.addEventListener('input', checkMatch);

  form.addEventListener('submit', function (e) {
    if (pw.value.length < minLength) {
      e.preventDefault();
      matchError.textContent = 'Password must be at least ' + minLength + ' characters.';
      pw.focus();
    } else if (pw.value !== confirmPw.value) {
      e.preventDefault();
      checkMatch();
      confirmPw.focus();
    }
  });
})();
