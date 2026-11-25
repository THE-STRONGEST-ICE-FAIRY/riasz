/* Small enhancements for reset.php. The server re-validates everything. */
(function () {
  'use strict';

  var form = document.getElementById('reset-form');
  if (!form) return;

  var pw = document.getElementById('password');
  var confirmPw = document.getElementById('confirm_password');
  var matchError = document.getElementById('match-error');
  var minLength = parseInt(pw.getAttribute('minlength'), 10) || 12;

  // Show / hide password
  var toggle = form.querySelector('.toggle-password');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      confirmPw.type = pw.type;
      toggle.querySelector('img').src = show ? toggle.dataset.srcShow : toggle.dataset.srcHide;
      toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  }

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
