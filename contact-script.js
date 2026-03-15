const form = document.getElementById('contact-form');
const btn = form.querySelector('button');
const errorsDiv = document.getElementById('form-errors');

function validateForm() {
  const errors = [];
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const message = document.getElementById('message').value.trim();

  // Name: 2+ chars, letters only
  if (name.length < 2) errors.push('Name must be 2+ characters.');
  else if (!/^[a-zA-Z\s]+$/.test(name)) errors.push('Name: letters only.');

  // Email: proper format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) errors.push('Invalid email format.');

  // Message: 10+ chars
  if (message.length < 10) errors.push('Message too short (10+ chars).');

  // Show/hide errors + toggle button
  if (errors.length) {
    errorsDiv.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
    errorsDiv.style.display = 'block';
    btn.disabled = true;
  } else {
    errorsDiv.style.display = 'none';
    btn.disabled = false;
  }

  return errors.length === 0;
}

// Real-time validation
['name', 'email', 'message'].forEach(id => {
  document.getElementById(id).addEventListener('blur', validateForm);
  document.getElementById(id).addEventListener('input', validateForm);
});

// Submit handler (unchanged)
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (!validateForm()) return;
    // ... existing fetch code
});