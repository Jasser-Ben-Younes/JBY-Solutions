const form = document.getElementById('contact-form');
const btn = form.querySelector('button');
const errorsDiv = document.getElementById('form-errors');

errorsDiv.style.display = 'none';

function validateForm() {
  const errors = [];
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const message = document.getElementById('message').value.trim();

  if (name.length < 2) errors.push('Name: 2+ chars');
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Invalid email');
  if (message.length < 10) errors.push('Message: 10+ chars');

  if (errors.length) {
    errorsDiv.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
    errorsDiv.style.display = 'block';
    btn.disabled = true;
    return false;
  }
  errorsDiv.style.display = 'none';
  btn.disabled = false;
  return true;
}

['name', 'email', 'message'].forEach(id => {
  document.getElementById(id).addEventListener('blur', validateForm);
  document.getElementById(id).addEventListener('input', () => {
    if (document.getElementById(id).value.trim().length) validateForm();
  });
});

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (!validateForm()) return;

  btn.textContent = 'Sending...';
  btn.disabled = true;

  try {
    const formData = new FormData(form);
    const response = await fetch('contact-process.php', {
      method: 'POST',
      body: formData
    });
    const result = await response.json();

    if (result.success) {
      alert('✅ Message sent! Check your email.');
      form.reset();
    } else {
      alert('❌ Error: ' + (result.error || 'Try again'));
    }
  } catch (err) {
    alert('❌ Network error—try again.');
  } finally {
    btn.textContent = 'Send message';
    validateForm();
  }
});