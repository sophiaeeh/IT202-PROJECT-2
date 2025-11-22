const $ = (id) => document.getElementById(id);
const focusAndFlag = (el) => { el.classList.add('input-error'); el.focus(); };
const clearFlags = () => document.querySelectorAll('.input-error').forEach(e => e.classList.remove('input-error'));

function normalizePhone(input) {
  const m = input.match(/^(\d{3})[- ](\d{3})[- ](\d{4})\s*(?:x|ext\.?|extension)\s*(\d+)$/i);
  return m ? `${m[1]}-${m[2]}-${m[3]} x${m[4]}` : null;
}

(function mountPasswordToggle(){
  const pwd = $('password');
  const eye = $('pwdToggle');
  if (!pwd || !eye) return;
  eye.addEventListener('click', () => {
    const isHidden = pwd.type === 'password';
    pwd.type = isHidden ? 'text' : 'password';
    eye.textContent = isHidden ? '🙈' : '👁️';
    eye.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
  });
})();

(function mountEmailToggle(){
  const wants = $('wantsEmail');
  const email = $('email');
  if (!wants || !email) return;
  function setEmailState() {
    const on = wants.checked;
    email.disabled = !on;
    email.required = on;
    email.placeholder = on ? 'name@example.com' : 'Check the box to enable email';
    email.parentElement.style.opacity = on ? 1 : 0.7;
    if (!on) {
      email.value = '';
      email.classList.remove('input-error');
    }
  }
  wants.addEventListener('change', setEmailState);
  setEmailState();
})();

function validate(form) {
  clearFlags();

  const firstName  = $('firstName');
  const lastName   = $('lastName');
  const phone      = $('phone');
  const catererId  = $('catererId');
  const email      = $('email');
  const wantsEmail = $('wantsEmail');
  const password   = $('password');
  const transaction= $('transaction');

  const nameRE  = /^[A-Za-z][A-Za-z' -]{1,}$/;
  const phoneRE = /^\d{3}[- ]\d{3}[- ]\d{4}\s*(?:x|ext\.?|extension)\s*\d+$/i;
  const idRE    = /^\d{4}$/;
  const emailRE = /^[^\s@]+@[^\s@]+\.[A-Za-z]{1,3}$/;
  const pwdRE   = /^(?=.*[A-Z])(?=.*\d)[^A-Za-z0-9].{0,4}$/;

  if (!transaction.value) {
    alert('Please choose a transaction from the dropdown.');
    focusAndFlag(transaction);
    return false;
  }

  if (!nameRE.test(firstName.value.trim())) {
    alert('Invalid first name. Use letters and optional apostrophes or hyphens.');
    focusAndFlag(firstName);
    return false;
  }

  if (!nameRE.test(lastName.value.trim())) {
    alert('Invalid last name. Use letters and optional apostrophes or hyphens.');
    focusAndFlag(lastName);
    return false;
  }

  if (!phoneRE.test(phone.value.trim())) {
    alert('Invalid phone. Use 973-555-1234 ext 789 (with extension).');
    focusAndFlag(phone);
    return false;
  }

  if (!idRE.test(catererId.value.trim())) {
    alert('Invalid Caterer ID. Use exactly 4 digits.');
    focusAndFlag(catererId);
    return false;
  }

  if (wantsEmail.checked && !emailRE.test(email.value.trim())) {
    alert('Invalid email. Use name@domain.tld with 1–3 letter TLD.');
    focusAndFlag(email);
    return false;
  }

  if (!pwdRE.test(password.value)) {
    alert('Invalid password. Must be 5 characters or fewer, start with a special character, and include at least one uppercase letter and one number.');
    focusAndFlag(password);
    return false;
  }

  const normalized = normalizePhone(phone.value.trim());
  if (!normalized) {
    alert('Phone formatting issue. Please use 973-555-1234 ext 789.');
    focusAndFlag(phone);
    return false;
  }
  phone.value = normalized;

  return true;
}

(function mountEvents(){
  const form = $('ccForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (validate(form)) {
      form.submit();
    }
  });

  const resetBtn = $('resetBtn');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      clearFlags();
      form.reset();
    });
  }

  const phone = $('phone');
  if (phone) {
    phone.addEventListener('blur', (evt) => {
      const normalized = normalizePhone(evt.target.value.trim());
      if (normalized) {
        evt.target.value = normalized;
      }
    });
  }
})();
