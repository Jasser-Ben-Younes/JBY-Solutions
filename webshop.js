(function () {
  const modal          = document.getElementById('checkout-modal');
  const productLabel   = document.getElementById('modal-product-label');
  const orderProduct   = document.getElementById('order-product');
  const orderPrice     = document.getElementById('order-price');
  const form           = document.getElementById('checkout-form');
  const errorList      = document.getElementById('order-errors');
  const successDiv     = document.getElementById('order-success');
  const stepContact    = document.getElementById('step-contact');
  const stepPayment    = document.getElementById('step-payment');
  const paymentSummary = document.getElementById('payment-summary-text');
  const backBtn        = document.getElementById('back-to-contact');

  let currentProduct = '';
  let currentPrice   = '';
  let paypalInstance = null;
  let lastError      = null; // stash error before PayPal's onError fires

  function openModal(product, price) {
    currentProduct = product;
    currentPrice   = price;

    productLabel.textContent = product + ' — ' + price;
    form.reset();
    orderProduct.value = product;
    orderPrice.value   = price;

    errorList.hidden   = true;
    errorList.innerHTML = '';
    stepContact.hidden = false;
    stepPayment.hidden = true;
    successDiv.hidden  = true;

    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal.hidden = true;
    document.body.style.overflow = '';
    if (paypalInstance) {
      paypalInstance.close();
      paypalInstance = null;
    }
  }

  function goToContact() {
    stepPayment.hidden = true;
    stepContact.hidden = false;
    if (paypalInstance) {
      paypalInstance.close();
      paypalInstance = null;
    }
  }

  function showError(msg) {
    errorList.innerHTML = `<li>${msg}</li>`;
    errorList.hidden = false;
  }

  // Wire up Buy Now buttons
  document.querySelectorAll('.btn-buy').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn.dataset.product, btn.dataset.price));
  });

  document.getElementById('modal-close').addEventListener('click', closeModal);
  document.getElementById('modal-done').addEventListener('click', closeModal);
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
  backBtn.addEventListener('click', goToContact);

  // Step 1: validate contact info, then show payment
  form.addEventListener('submit', e => {
    e.preventDefault();

    const name  = form.elements['name'].value.trim();
    const email = form.elements['email'].value.trim();
    const errors = [];

    if (name.length < 2)
      errors.push('Please enter your full name.');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
      errors.push('Please enter a valid email address.');

    if (errors.length) {
      errorList.innerHTML = errors.map(m => `<li>${m}</li>`).join('');
      errorList.hidden = false;
      return;
    }

    errorList.hidden = true;
    stepContact.hidden = true;
    stepPayment.hidden = false;
    paymentSummary.textContent = currentProduct + ' — ' + currentPrice;

    // Clear any previous render
    document.getElementById('paypal-button-container').innerHTML = '';

    // Step 2: render PayPal buttons
    paypalInstance = paypal.Buttons({
      style: {
        layout: 'vertical',
        color:  'blue',
        shape:  'pill',
        label:  'pay',
        tagline: false,
      },

      createOrder: async () => {
        try {
          const body = new FormData();
          body.append('product', currentProduct);
          body.append('price',   currentPrice);
          const res  = await fetch('paypal-create-order.php', { method: 'POST', body });
          const json = await res.json();
          if (json.error) {
            lastError = 'Order creation failed: ' + json.error;
            throw new Error(json.error);
          }
          return json.orderID;
        } catch (err) {
          if (!lastError) lastError = 'Network error reaching server: ' + err.message;
          throw err;
        }
      },

      onApprove: async data => {
        try {
          const body = new FormData();
          body.append('orderID', data.orderID);
          body.append('product', currentProduct);
          body.append('price',   currentPrice);
          body.append('name',    form.elements['name'].value.trim());
          body.append('email',   form.elements['email'].value.trim());

          const res  = await fetch('paypal-capture-order.php', { method: 'POST', body });
          const json = await res.json();

          if (json.success) {
            stepPayment.hidden = true;
            successDiv.hidden  = false;
          } else {
            goToContact();
            showError('Capture failed: ' + (json.error || 'unknown error'));
          }
        } catch (err) {
          goToContact();
          showError('Network error during capture: ' + err.message);
        }
      },

      onError: (err) => {
        goToContact();
        const msg = lastError || (err && err.message) || 'Unknown PayPal error';
        showError('Payment error — ' + msg);
        console.error('[PayPal onError]', err);
        lastError = null;
      },

      onCancel: () => {
        goToContact();
      },
    });

    paypalInstance.render('#paypal-button-container');
  });
})();
