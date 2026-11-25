/* RIAS login page
 * 1 helpers  2 landing page  3 legal modal  4 auth modal (sign in / OTP / forgot)  5 boot
 * All server talk goes through api() -> handler.php. */
(function () {
  'use strict';

  /* ======================================================================
   * 1. Helpers
   * ==================================================================== */
  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var nowSec = function () { return Math.floor(Date.now() / 1000); };

  var store = {
    get: function (k) { try { return sessionStorage.getItem(k); } catch (e) { return null; } },
    set: function (k, v) { try { sessionStorage.setItem(k, String(v)); } catch (e) { /* private mode */ } },
    del: function (k) { try { sessionStorage.removeItem(k); } catch (e) { /* ignore */ } },
    int: function (k) { var n = parseInt(store.get(k), 10); return isNaN(n) ? 0 : n; }
  };

  var KEY = {
    legal: 'rias.legalAccepted',
    otpEmail: 'rias.otpEmail',
    otpExpires: 'rias.otpExpiresAt',
    otpResend: 'rias.otpResendAt',
    otpLock: 'rias.otpLockUntil',
    forgot: 'rias.forgotUntil'
  };

  var DOMAINS = (document.body.dataset.domains || '').split(',').filter(Boolean);
  var CSRF = ($('meta[name="csrf-token"]') || {}).content || '';

  function getTabId() {
    var id = store.get('tabId');
    if (!id) {
      if (window.crypto && crypto.randomUUID) {
        id = crypto.randomUUID();
      } else {
        var b = new Uint8Array(16);
        crypto.getRandomValues(b);
        id = Array.prototype.map.call(b, function (x) { return ('0' + x.toString(16)).slice(-2); }).join('');
      }
      store.set('tabId', id);
    }
    return id;
  }

  function emailAllowed(email) {
    if (!DOMAINS.length) return true;
    email = email.toLowerCase();
    return DOMAINS.some(function (d) { return email.slice(-d.length) === d.toLowerCase(); });
  }

  function domainMessage() {
    return 'Invalid email address. Please use ' + DOMAINS.join(' or ') + '.';
  }

  function fmtWait(sec) {
    sec = Math.max(0, sec);
    var m = Math.floor(sec / 60), s = sec % 60;
    return m ? m + 'm ' + (s < 10 ? '0' : '') + s + 's' : s + 's';
  }

  /* One request helper: always resolves to an object {status, message, ...}. */
  function api(action, payload) {
    var body = Object.assign({ action: action, tabId: getTabId() }, payload || {});
    return fetch('handler.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify(body)
    }).then(function (res) {
      return res.json().catch(function () { return { status: 'error', message: 'Unexpected server response.' }; })
        .then(function (data) { data.httpStatus = res.status; return data; });
    }).catch(function () {
      return { status: 'error', code: 'network', message: 'Connection error. Please try again later.' };
    });
  }

  /* Blocks clicks while a request runs. `busy` also guards against double-submits from the keyboard. */
  var blocker = $('#click-blocker');
  var busy = false;
  function setBusy(on) {
    busy = on;
    blocker.hidden = !on;
  }

  function clearOtpState() {
    [KEY.otpEmail, KEY.otpExpires, KEY.otpResend, KEY.otpLock].forEach(store.del);
  }

  /* ======================================================================
   * 2. Landing page
   * ==================================================================== */
  var nav = $('.nav');
  var header = $('.header');
  var headerH = header.offsetHeight;
  var indicator = $('.scroll-indicator');

  var scrollTick = false;
  var indicatorArmed = false;

  function onScroll() {
    if (scrollTick) return;
    scrollTick = true;
    requestAnimationFrame(function () {
      scrollTick = false;
      nav.classList.toggle('fixed', window.scrollY > headerH);
      indicator.classList.toggle('visible', indicatorArmed && window.scrollY === 0);
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Scroll indicator appears 3 seconds after load, while at the top.
  window.addEventListener('load', function () {
    setTimeout(function () { indicatorArmed = true; onScroll(); }, 3000);
  });

  $('.hero-content').classList.add('float-in');

  // Collapsible sections (click or Enter/Space on the heading)
  $$('.content').forEach(function (section) {
    var h = $('h1', section);
    h.tabIndex = 0;
    h.setAttribute('role', 'button');
    h.setAttribute('aria-expanded', 'false');

    function toggle() {
      var open = section.classList.toggle('active');
      h.setAttribute('aria-expanded', String(open));
    }
    h.addEventListener('click', toggle);
    h.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
    });
  });

  function openSection(id) {
    var section = document.getElementById(id);
    if (!section) return;
    section.classList.add('active');
    $('h1', section).setAttribute('aria-expanded', 'true');
    // wait for the expand transition (0.3s) so the final position is right
    setTimeout(function () {
      var top = section.getBoundingClientRect().top + window.scrollY - headerH - 10;
      window.scrollTo({ top: top, behavior: 'smooth' });
    }, 300);
  }

  // Any in-page link (#about, #privacy-policy, ...) from nav, footer or modals
  document.addEventListener('click', function (e) {
    var link = e.target.closest('a[href^="#"]');
    if (!link || link.id === 'open-sign-in') return;

    var id = link.getAttribute('href').slice(1);
    if (!id || !document.getElementById(id)) return;

    e.preventDefault();
    if (auth.isOpen()) {
      auth.close();
      setTimeout(function () { openSection(id); }, 350);
    } else {
      openSection(id);
    }
  });

  /* ======================================================================
   * 3. Legal modal (privacy policy + terms, shown before first sign-in)
   * ==================================================================== */
  var legal = (function () {
    var modal = $('#privacy-policy-modal');
    var box = $('.scrollable-box', modal);
    var agree = $('#agree-checkbox');
    var proceed = $('#proceed-btn');
    var hint = $('#scroll-hint');

    function scrolledToEnd() {
      return box.scrollHeight <= box.clientHeight + 1 ||
        box.scrollTop + box.clientHeight >= box.scrollHeight - 2;
    }

    function refresh() {
      var done = scrolledToEnd();
      agree.disabled = !done;
      hint.hidden = done;
      if (!done) { agree.checked = false; }
      proceed.disabled = !(done && agree.checked);
    }

    function open() {
      agree.checked = false;
      modal.classList.add('open');
      document.body.classList.add('modal-open');
      box.scrollTop = 0;
      refresh();
    }

    function close() {
      modal.classList.remove('open');
      document.body.classList.remove('modal-open');
    }

    box.addEventListener('scroll', refresh);
    window.addEventListener('resize', refresh);
    agree.addEventListener('change', refresh);
    $('.close-btn', modal).addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

    proceed.addEventListener('click', function () {
      store.set(KEY.legal, '1');
      close();
      auth.open();
    });

    return { open: open, close: close, isOpen: function () { return modal.classList.contains('open'); } };
  })();

  /* ======================================================================
   * 4. Auth modal (sign in / OTP / forgot password)
   * ==================================================================== */
  var auth = (function () {
    var modal = $('#sign-in-modal');
    var content = $('.modal-content', modal);
    var timers = [];
    var current = null;

    function every(fn, ms) {
      var id = setInterval(fn, ms);
      timers.push(id);
      fn();
      return id;
    }
    function clearTimers() {
      timers.forEach(clearInterval);
      timers = [];
    }

    /* --- view plumbing --- */
    function show(name, ctx) {
      clearTimers();
      var tpl = $('#tpl-' + name);
      content.innerHTML = '';
      content.appendChild(tpl.content.cloneNode(true));
      current = name;

      content.scrollTop = 0;
      $('[data-close]', content).addEventListener('click', close);
      $$('[data-goto]', content).forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); route(a.dataset.goto); });
      });

      var form = $('[data-form]', content);
      views[name](content, form, ctx || {});

      var first = $('input', content);
      if (first && !first.disabled) first.focus();
    }

    /* Decide which OTP screen to show based on what we remember. */
    function route(name) {
      if (name === 'otp') {
        var live = store.get(KEY.otpEmail) && store.int(KEY.otpExpires) > nowSec();
        show(live ? 'otp-verify' : 'otp-request');
      } else {
        show(name);
      }
    }

    function open() {
      if (!store.get(KEY.legal)) {
        legal.open();
        return;
      }
      modal.classList.add('open');
      document.body.classList.add('modal-open', 'auth-open');
      show('signin');
    }

    function close() {
      clearTimers();
      modal.classList.remove('open');
      document.body.classList.remove('modal-open', 'auth-open');
    }

    function isOpen() { return modal.classList.contains('open'); }

    /* --- small form helpers --- */
    function err(root, name, msg) {
      var el = $('[data-error="' + name + '"]', root);
      if (el) el.textContent = msg || '';
      var input = $('[name="' + name + '"]', root);
      if (input) input.classList.toggle('invalid', !!msg);
    }
    function clearErrors(root) {
      $$('[data-error]', root).forEach(function (el) { el.textContent = ''; });
      $$('input.invalid', root).forEach(function (i) { i.classList.remove('invalid'); });
    }
    function submitLabel(btn, text) {
      if (text) { btn.dataset.label = btn.dataset.label || btn.textContent; btn.textContent = text; }
      else if (btn.dataset.label) { btn.textContent = btn.dataset.label; }
    }
    function goTo(url) { window.location.replace(url); }

    /* Map a failed API reply onto the right spot in the form. */
    function showApiError(root, res, fallbackField) {
      var field = res.field && $('[data-error="' + res.field + '"]', root) ? res.field : fallbackField || 'form';
      err(root, field, res.message || 'An error occurred. Please try again.');
    }

    /* --- password visibility toggle --- */
    function wireToggle(root) {
      var btn = $('.toggle-password', root);
      if (!btn) return;
      var input = $('input[type="password"], input[data-pw]', root);
      btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        input.dataset.pw = '1';
        $('img', btn).src = show ? btn.dataset.srcShow : btn.dataset.srcHide;
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      });
    }

    /* ------------------------------------------------------------------
     * Views
     * ---------------------------------------------------------------- */
    var views = {};

    views.signin = function (root, form) {
      $('[data-tooltip]', root).textContent = 'Allowed: ' + DOMAINS.join(', ');
      wireToggle(root);

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (busy) return;
        clearErrors(form);

        var email = form.email.value.trim();
        var password = form.password.value;
        var ok = true;

        if (!email || !emailAllowed(email)) { err(form, 'email', domainMessage()); ok = false; }
        if (!password) { err(form, 'password', 'Please enter your password.'); ok = false; }
        if (!ok) return;

        var btn = $('button[type="submit"]', form);
        btn.disabled = true;
        submitLabel(btn, 'Signing in...');
        setBusy(true);

        api('login', { email: email, password: password }).then(function (res) {
          setBusy(false);
          if (res.status === 'success' && res.redirect) {
            clearOtpState();
            goTo(res.redirect);
            return;
          }
          btn.disabled = false;
          submitLabel(btn);
          showApiError(form, res, 'password');
          form.password.value = '';
        });
      });
    };

    views['otp-request'] = function (root, form, ctx) {
      if (ctx.message) err(form, 'form', ctx.message);

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (busy) return;
        clearErrors(form);

        var email = form.email.value.trim();
        if (!email || !emailAllowed(email)) { err(form, 'email', domainMessage()); return; }

        var btn = $('button[type="submit"]', form);
        btn.disabled = true;
        submitLabel(btn, 'Sending...');
        setBusy(true);

        api('sendotp', { email: email }).then(function (res) {
          setBusy(false);
          btn.disabled = false;
          submitLabel(btn);

          if (res.status === 'success') {
            store.set(KEY.otpEmail, email);
            store.set(KEY.otpExpires, nowSec() + (res.expires_in || 600));
            store.set(KEY.otpResend, nowSec() + (res.resend_after || 60));
            show('otp-verify');
          } else {
            if (res.code === 'throttled' && res.retry_after) {
              store.set(KEY.otpLock, nowSec() + res.retry_after);
            }
            showApiError(form, res, 'email');
          }
        });
      });
    };

    views['otp-verify'] = function (root, form) {
      var email = store.get(KEY.otpEmail) || '';
      var input = form.otp;
      var verifyBtn = $('[data-verify]', form);
      var resendBtn = $('[data-resend]', form);

      $('[data-notice]', root).textContent =
        'If ' + email + ' is registered, a 6-digit code was sent. Check your inbox.';

      $('[data-action="change-email"]', root).addEventListener('click', function (e) {
        e.preventDefault();
        clearOtpState();
        show('otp-request');
      });

      // Digits only
      input.addEventListener('input', function () {
        input.value = input.value.replace(/\D/g, '').slice(0, 6);
      });

      // One ticking timer drives: code validity countdown, resend cooldown, failed-attempt lockout.
      every(function () {
        var now = nowSec();
        var left = store.int(KEY.otpExpires) - now;
        var lock = store.int(KEY.otpLock) - now;
        var resendIn = store.int(KEY.otpResend) - now;

        if (left <= 0) {                       // the code expired
          clearOtpState();
          show('otp-request', { message: 'That code expired. Request a new one.' });
          return;
        }

        if (lock > 0) {                        // too many wrong attempts
          input.disabled = true;
          verifyBtn.disabled = true;
          resendBtn.disabled = true;
          submitLabel(verifyBtn, 'VERIFY OTP');
          err(form, 'form', 'Too many failed attempts. Wait ' + fmtWait(lock) + '.');
          return;
        }

        if (input.disabled && !busy) {         // lockout just ended
          input.disabled = false;
          err(form, 'form', '');
          clearOtpState();
          show('otp-request', { message: 'You can request a new code now.' });
          return;
        }

        if (!busy) verifyBtn.disabled = false;
        submitLabel(verifyBtn, 'VERIFY OTP (' + fmtWait(left) + ')');
        resendBtn.disabled = resendIn > 0;
        resendBtn.textContent = resendIn > 0 ? 'Resend OTP (' + resendIn + 's)' : 'Resend OTP';
      }, 1000);

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (busy || input.disabled) return;
        clearErrors(form);

        var code = input.value.trim();
        if (!/^\d{6}$/.test(code)) { err(form, 'otp', 'Enter the 6-digit code.'); return; }

        verifyBtn.disabled = true;
        setBusy(true);

        api('useotp', { otp: code }).then(function (res) {
          setBusy(false);

          if (res.status === 'success' && res.redirect) {
            clearOtpState();
            goTo(res.redirect);
            return;
          }

          if (res.code === 'throttled' && res.retry_after) {
            store.set(KEY.otpLock, nowSec() + res.retry_after);
          } else if (res.code === 'no_pending') {
            clearOtpState();
            show('otp-request', { message: res.message });
            return;
          }
          verifyBtn.disabled = false;
          input.value = '';
          input.focus();
          showApiError(form, res, 'otp');
        });
      });

      resendBtn.addEventListener('click', function () {
        if (busy || resendBtn.disabled) return;
        clearErrors(form);
        resendBtn.disabled = true;
        resendBtn.textContent = 'Sending...';
        setBusy(true);

        api('sendotp', { email: email }).then(function (res) {
          setBusy(false);
          if (res.status === 'success') {
            store.set(KEY.otpExpires, nowSec() + (res.expires_in || 600));
            store.set(KEY.otpResend, nowSec() + (res.resend_after || 60));
            show('otp-verify');
          } else {
            if (res.code === 'throttled' && res.retry_after) {
              store.set(KEY.otpResend, nowSec() + res.retry_after);
            }
            showApiError(form, res, 'form');
          }
        });
      });
    };

    views.forgot = function (root, form) {
      var emailInput = form.email;
      var btn = $('button[type="submit"]', form);
      var sent = $('[data-sent]', root);
      var intro = $('[data-intro]', root);

      // Cooldown after a request (survives closing and reopening the modal)
      every(function () {
        var wait = store.int(KEY.forgot) - nowSec();
        if (wait > 0) {
          emailInput.disabled = true;
          btn.disabled = true;
          err(form, 'form', 'Please wait ' + fmtWait(wait) + ' before trying again.');
        } else if (emailInput.disabled && !busy) {
          emailInput.disabled = false;
          btn.disabled = false;
          err(form, 'form', '');
          store.del(KEY.forgot);
          sent.hidden = true;
          intro.hidden = false;
        }
      }, 1000);

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (busy || emailInput.disabled) return;
        clearErrors(form);

        var email = emailInput.value.trim();
        if (!email || !emailAllowed(email)) { err(form, 'email', domainMessage()); return; }

        btn.disabled = true;
        submitLabel(btn, 'Sending...');
        setBusy(true);

        api('resetp', { email: email }).then(function (res) {
          setBusy(false);
          submitLabel(btn);

          if (res.status === 'success') {
            store.set(KEY.forgot, nowSec() + (res.retry_after || 300));
            intro.hidden = true;
            sent.hidden = false;
            sent.textContent = res.message + ' Check your inbox (and spam folder).';
          } else {
            btn.disabled = false;
            if (res.code === 'throttled' && res.retry_after) {
              store.set(KEY.forgot, nowSec() + res.retry_after);
            }
            showApiError(form, res, 'email');
          }
        });
      });
    };

    // Esc closes whichever modal is open
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape' || busy) return;
      if (isOpen()) close();
      else if (legal.isOpen()) legal.close();
    });

    // Clicking the dark backdrop closes the sign-in modal too
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

    return { open: open, close: close, isOpen: isOpen };
  })();

  /* ======================================================================
   * 5. Boot
   * ==================================================================== */

  // "SIGN IN" in the nav: scroll to the top first, then open.
  $('#open-sign-in').addEventListener('click', function (e) {
    e.preventDefault();
    if (window.scrollY === 0) { auth.open(); return; }

    window.scrollTo({ top: 0, behavior: 'smooth' });
    var started = Date.now();
    (function wait() {
      if (window.scrollY === 0 || Date.now() - started > 1500) auth.open();
      else requestAnimationFrame(wait);
    })();
  });

  // If this browser tab is already signed in, skip the form.
  api('whoami').then(function (res) {
    if (res.authenticated && res.redirect) window.location.replace(res.redirect);
  });
})();
