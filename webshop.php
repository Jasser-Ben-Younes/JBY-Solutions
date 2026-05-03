<?php $catalogue = require __DIR__ . '/products.php';
$gcode = $catalogue['G-Code Post-Processor'];
$kuka  = $catalogue['KUKA KRL G-Code Post-Processor'];
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JBY Solutions - Shop</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

  <header></header>
  <main>
    <section class="shop-section">
      <h1>Shop</h1>
      <p>Engineering tools and software by JBY Solutions.</p>

      <div class="product-grid">

        <div class="product-card">
          <div class="product-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/>
            </svg>
          </div>
          <div class="product-body">
            <h2>G-Code Post-Processor</h2>
            <p>Converts CAM output to clean, machine-ready G-Code. Compatible with standard CNC controllers.</p>
            <span class="product-price"><?= htmlspecialchars($gcode['display']) ?></span>
          </div>
          <div class="card-actions">
            <a class="btn-secondary" href="product-gcode.php">Preview</a>
            <button class="btn-primary btn-buy"
                    data-product="G-Code Post-Processor"
                    data-price="<?= htmlspecialchars($gcode['display']) ?>">
              Buy Now
            </button>
          </div>
        </div>

        <div class="product-card">
          <div class="product-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2v-4M9 21H5a2 2 0 0 1-2-2v-4m0 0h18"/>
            </svg>
          </div>
          <div class="product-body">
            <h2>KUKA KRL G-Code Post-Processor</h2>
            <p>Translates G-Code to KUKA Robot Language (KRL) for seamless integration with KUKA robot controllers.</p>
            <span class="product-price"><?= htmlspecialchars($kuka['display']) ?></span>
          </div>
          <div class="card-actions">
            <a class="btn-secondary" href="product-kuka.php">Preview</a>
            <button class="btn-primary btn-buy"
                    data-product="KUKA KRL G-Code Post-Processor"
                    data-price="<?= htmlspecialchars($kuka['display']) ?>">
              Buy Now
            </button>
          </div>
        </div>

      </div>
    </section>

    <!-- Checkout modal -->
    <div id="checkout-modal" class="modal-overlay" hidden>
      <div class="modal-box">
        <button class="modal-close" id="modal-close" aria-label="Close">&times;</button>
        <h2>Complete your order</h2>
        <p id="modal-product-label" class="modal-product-name"></p>

        <!-- Step 1: Contact details -->
        <div id="step-contact">
          <form id="checkout-form" novalidate>
            <input type="hidden" id="order-product" name="product" />
            <input type="hidden" id="order-price"   name="price"   />
            <div class="form-group">
              <label for="order-name">Full Name</label>
              <input type="text"  id="order-name"  name="name"  autocomplete="name" />
            </div>
            <div class="form-group">
              <label for="order-email">Email Address</label>
              <input type="email" id="order-email" name="email" autocomplete="email" />
            </div>
            <ul id="order-errors" class="error-list" hidden></ul>
            <button type="submit" class="btn-primary btn-large shop-submit" id="order-submit">
              Continue to Payment
            </button>
          </form>
        </div>

        <!-- Step 2: Payment -->
        <div id="step-payment" hidden>
          <button class="payment-back-btn" id="back-to-contact">&#8592; Edit details</button>
          <div class="payment-summary-box">
            Paying for: <strong id="payment-summary-text"></strong>
          </div>
          <div id="paypal-button-container"></div>
        </div>

        <!-- Success -->
        <div id="order-success" class="success-section" hidden>
          <div class="success-content">
            <svg class="success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
            <h3>Payment confirmed!</h3>
            <p>Thanks for your purchase. We'll send your download link and invoice to your email shortly.</p>
            <button class="btn-primary" id="modal-done">Close</button>
          </div>
        </div>

      </div>
    </div>
  </main>
  <footer></footer>

  <script src="main.js"></script>
  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
  <script src="inject-shared.js"></script>
  <script src="https://www.paypal.com/sdk/js?client-id=AezzTHzS_1xMbOkSrdrQlQW7haRJz_Q2j1ZOtZqXy4ycN-2a7u1Es0hY8cj_lVhSMW8aRdzDIryWNmDC&currency=EUR&intent=capture"></script>
  <script src="webshop.js"></script>
</body>
</html>
