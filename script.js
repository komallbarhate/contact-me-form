/**
 * script.js — Contact Form with EmailJS
 *
 * HOW TO ACTIVATE EMAIL SENDING (one-time setup, ~5 minutes):
 * ─────────────────────────────────────────────────────────────
 * 1. Go to https://www.emailjs.com  →  Sign Up (free)
 * 2. Dashboard → Email Services → Add New Service → Gmail
 *    → Connect your Gmail (barhate.komal12@gmail.com) → Copy the Service ID
 * 3. Dashboard → Email Templates → Create New Template
 *    Paste this template body:
 *
 *      From : {{from_name}} ({{from_email}})
 *      Subject : {{subject}}
 *
 *      {{message}}
 *
 *    Set "To Email" to  barhate.komal12@gmail.com  → Save → Copy the Template ID
 * 4. Dashboard → Account → Copy your Public Key
 * 5. Fill in the three values below and save the file. That's it!
 * ─────────────────────────────────────────────────────────────
 */

(function () {
  'use strict';

  // ─── ✏️  FILL THESE IN AFTER EMAILJS SETUP ────────────────────────────────
  const EMAILJS_PUBLIC_KEY  = 'zhFc6JFtjL9s4FbjM';   // Account → Public Key
  const EMAILJS_SERVICE_ID  = 'service_3f68gqn';   // Email Services → Service ID
  const EMAILJS_TEMPLATE_ID = 'template_6iowijk';  // Email Templates → Template ID
  // ──────────────────────────────────────────────────────────────────────────

  // Initialise EmailJS with your public key
  emailjs.init({ publicKey: EMAILJS_PUBLIC_KEY });

  // ─── Element References ───────────────────────────────────────────────────
  const form         = document.getElementById('contact-form');
  const submitBtn    = document.getElementById('submit-btn');
  const submitStatus = document.getElementById('submit-status');
  const successMsg   = document.getElementById('success-message');
  const messageArea  = document.getElementById('message');
  const charCounter  = document.getElementById('message-counter');

  // ─── Validation Rules ─────────────────────────────────────────────────────
  const validators = {
    name: {
      required: true,
      minLength: 2,
      pattern: /^[a-zA-Z\s'\-]+$/,
      messages: {
        required:  'Full name is required.',
        minLength: 'Name must be at least 2 characters.',
        pattern:   'Name may only contain letters, spaces, hyphens, or apostrophes.',
      },
    },
    email: {
      required: true,
      pattern:  /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
      messages: {
        required: 'Email address is required.',
        pattern:  'Please enter a valid email address.',
      },
    },
    message: {
      required:  true,
      minLength: 10,
      maxLength: 1000,
      messages: {
        required:  'A message is required.',
        minLength: 'Please write at least 10 characters.',
        maxLength: 'Message must not exceed 1000 characters.',
      },
    },
  };

  // ─── Validate a Single Field ──────────────────────────────────────────────
  function validateField(input) {
    const id    = input.id;
    const rules = validators[id];
    const value = input.value.trim();
    if (!rules) return '';
    if (rules.required && value === '')                            return rules.messages.required;
    if (rules.minLength && value.length < rules.minLength && value !== '') return rules.messages.minLength;
    if (rules.maxLength && value.length > rules.maxLength)        return rules.messages.maxLength;
    if (rules.pattern && value !== '' && !rules.pattern.test(value)) return rules.messages.pattern;
    return '';
  }

  // ─── Update Field UI State ────────────────────────────────────────────────
  function updateFieldState(input, errorMessage, showFeedback) {
    const group      = input.closest('.form-group');
    const errorEl    = document.getElementById(`${input.id}-error`);
    const statusIcon = document.getElementById(`${input.id}-status`);
    if (!group) return;

    group.classList.remove('valid', 'invalid', 'shake');

    if (!showFeedback || input.value.trim() === '') {
      if (errorEl)    { errorEl.textContent = ''; errorEl.classList.remove('visible'); }
      if (statusIcon) { statusIcon.textContent = ''; statusIcon.classList.remove('show'); }
      return;
    }

    if (errorMessage) {
      group.classList.add('invalid');
      if (errorEl)    { errorEl.textContent = errorMessage; errorEl.classList.add('visible'); }
      if (statusIcon) { statusIcon.textContent = '✗'; statusIcon.style.color = 'var(--color-error)'; statusIcon.classList.add('show'); }
      input.setAttribute('aria-invalid', 'true');
    } else {
      group.classList.add('valid');
      if (errorEl)    { errorEl.textContent = ''; errorEl.classList.remove('visible'); }
      if (statusIcon) { statusIcon.textContent = '✓'; statusIcon.style.color = 'var(--color-success)'; statusIcon.classList.add('show'); }
      input.removeAttribute('aria-invalid');
    }
  }

  // ─── Shake on Invalid ─────────────────────────────────────────────────────
  function shakeField(input) {
    const group = input.closest('.form-group');
    if (!group) return;
    group.classList.remove('shake');
    void group.offsetWidth;
    group.classList.add('shake');
    group.addEventListener('animationend', () => group.classList.remove('shake'), { once: true });
  }

  // ─── Character Counter ────────────────────────────────────────────────────
  function updateCharCounter() {
    const length = messageArea.value.length;
    charCounter.textContent = `${length} / 1000`;
    charCounter.classList.remove('near-limit', 'at-limit');
    if (length >= 1000)      charCounter.classList.add('at-limit');
    else if (length >= 850)  charCounter.classList.add('near-limit');
  }

  // ─── Live Validation Listeners ────────────────────────────────────────────
  ['name', 'email', 'message'].forEach(id => {
    const input = document.getElementById(id);
    if (!input) return;
    let hasBlurred = false;

    input.addEventListener('blur', () => {
      hasBlurred = true;
      updateFieldState(input, validateField(input), true);
    });

    input.addEventListener('input', () => {
      if (hasBlurred) updateFieldState(input, validateField(input), true);
      if (id === 'message') updateCharCounter();
    });
  });

  // ─── Send Email via EmailJS ───────────────────────────────────────────────
  function sendEmail(name, email, subject, message) {
    const templateParams = {
      from_name:  name,
      from_email: email,
      subject:    subject || '(No subject)',
      message:    message,
      to_email:   'barhate.komal12@gmail.com',
    };

    return emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, templateParams);
  }

  // ─── Form Submit ──────────────────────────────────────────────────────────
  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    // Check if keys have been filled in
    if (
      EMAILJS_PUBLIC_KEY  === 'YOUR_PUBLIC_KEY'  ||
      EMAILJS_SERVICE_ID  === 'YOUR_SERVICE_ID'  ||
      EMAILJS_TEMPLATE_ID === 'YOUR_TEMPLATE_ID'
    ) {
      alert(
        '⚠️ EmailJS is not configured yet.\n\n' +
        'Open script.js and fill in your:\n' +
        '  • EMAILJS_PUBLIC_KEY\n' +
        '  • EMAILJS_SERVICE_ID\n' +
        '  • EMAILJS_TEMPLATE_ID\n\n' +
        'See the instructions at the top of script.js for details.\n' +
        'Setup takes about 5 minutes at https://www.emailjs.com'
      );
      return;
    }

    // Validate all required fields
    let isFormValid = true;
    ['name', 'email', 'message'].forEach(id => {
      const input = document.getElementById(id);
      if (!input) return;
      const err = validateField(input);
      updateFieldState(input, err, true);
      if (err) { isFormValid = false; shakeField(input); }
    });

    if (!isFormValid) {
      const firstInvalid = form.querySelector('.form-group.invalid .form-input');
      if (firstInvalid) firstInvalid.focus();
      submitStatus.textContent = 'Please fix the errors before submitting.';
      return;
    }

    // Start loading
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    submitStatus.textContent = 'Sending your message…';

    const name    = document.getElementById('name').value.trim();
    const email   = document.getElementById('email').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();

    try {
      await sendEmail(name, email, subject, message);

      // ✅ Success
      successMsg.setAttribute('aria-hidden', 'false');
      successMsg.classList.add('visible');
      submitStatus.textContent = 'Message sent successfully!';
      form.reset();
      updateCharCounter();
      ['name', 'email', 'message'].forEach(id => {
        const input = document.getElementById(id);
        if (input) updateFieldState(input, '', false);
      });

    } catch (err) {
      console.error('EmailJS error:', err);
      submitStatus.textContent = 'Failed to send. Please try again.';
      alert(
        '❌ Could not send your message.\n\n' +
        'Error: ' + (err.text || err.message || JSON.stringify(err)) + '\n\n' +
        'Please check your EmailJS keys in script.js and try again.'
      );
    } finally {
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
    }
  });

  // Initialise counter
  updateCharCounter();

})();
