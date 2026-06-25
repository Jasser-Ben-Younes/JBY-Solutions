<?php
session_start();

define('ALLOWED_IPS', [
    '176.2.99.9',
    '209.198.140.68',
    '2a0d:3341:b908:c908:2d72:c17c:8684:c2dc',
]);

$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$client_ip = trim(explode(',', $client_ip)[0]);

if (!in_array($client_ip, ALLOWED_IPS, true) || empty($_SESSION['kuka_auth'])) {
    header('Location: login.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JBY Solutions - KUKA Post-Processor</title>
  <link rel="stylesheet" href="/style.css" />
  <style>
    .kuka-tool-section {
      max-width: 680px;
      margin: 0 auto;
      padding: 2rem 0 4rem;
    }

    .kuka-tool-section h1 {
      font-size: 1.9rem;
      margin-bottom: 0.4rem;
      color: #111827;
    }

    .kuka-tool-section .subtitle {
      color: #64748b;
      margin-bottom: 2rem;
      font-size: 0.97rem;
    }

    /* Drop zone */
    .drop-zone {
      border: 2px dashed #94a3b8;
      border-radius: 12px;
      padding: 2.8rem 2rem;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      background: #fff;
      margin-bottom: 1rem;
    }

    .drop-zone.drag-over {
      border-color: #2563eb;
      background: #eff6ff;
    }

    .drop-zone.has-file {
      border-color: #22c55e;
      background: #f0fdf4;
    }

    .drop-zone svg {
      width: 40px;
      height: 40px;
      stroke: #94a3b8;
      margin-bottom: 0.75rem;
    }

    .drop-zone.has-file svg {
      stroke: #22c55e;
    }

    .drop-zone p {
      color: #64748b;
      font-size: 0.95rem;
      margin-bottom: 0.5rem;
    }

    .drop-zone .file-name {
      font-weight: 600;
      color: #111827;
      font-size: 0.95rem;
      word-break: break-all;
    }

    .browse-link {
      color: #2563eb;
      text-decoration: underline;
      cursor: pointer;
      font-size: 0.9rem;
    }

    /* Params */
    .params-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 0.75rem;
      margin-bottom: 1.25rem;
    }

    .param-group label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.3rem;
    }

    .param-group input {
      width: 100%;
      padding: 0.5rem 0.75rem;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 0.93rem;
      background: #fff;
      color: #111827;
      transition: border-color 0.15s;
    }

    .param-group input:focus {
      outline: none;
      border-color: #2563eb;
    }

    /* Progress */
    .progress-wrap {
      margin-bottom: 1.25rem;
    }

    .progress-bar-bg {
      background: #e5e7eb;
      border-radius: 999px;
      height: 10px;
      overflow: hidden;
    }

    .progress-bar-fill {
      height: 100%;
      width: 0%;
      background: #2563eb;
      border-radius: 999px;
      transition: width 0.1s linear;
    }

    .progress-label {
      font-size: 0.83rem;
      color: #64748b;
      margin-top: 0.4rem;
      text-align: right;
    }

    /* Actions */
    .action-row {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    .btn-start {
      padding: 0.65rem 1.6rem;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 999px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s, opacity 0.2s;
    }

    .btn-start:disabled {
      opacity: 0.45;
      cursor: not-allowed;
    }

    .btn-start:not(:disabled):hover {
      background: #1d4ed8;
    }

    .btn-download {
      display: none;
      align-items: center;
      gap: 0.45rem;
      padding: 0.65rem 1.6rem;
      background: #16a34a;
      color: #fff;
      border: none;
      border-radius: 999px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      text-decoration: none;
    }

    .btn-download.visible {
      display: inline-flex;
    }

    .btn-download:hover {
      background: #15803d;
    }

    .btn-download svg {
      width: 17px;
      height: 17px;
      stroke: #fff;
      flex-shrink: 0;
    }

    .status-msg {
      margin-top: 1rem;
      font-size: 0.88rem;
      color: #64748b;
      min-height: 1.2em;
    }

    .status-msg.error {
      color: #dc2626;
    }

    .status-msg.success {
      color: #16a34a;
      font-weight: 600;
    }

    .back-link {
      display: inline-block;
      color: #2563eb;
      text-decoration: none;
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 520px) {
      .params-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <header></header>
  <main>
    <section class="kuka-tool-section">

      <a class="back-link" href="/webshop.php">&#8592; Back to Shop</a>

      <h1>KUKA Post-Processor</h1>
      <p class="subtitle">Convert your G-Code toolpath into KUKA KRL (.SRC + .DAT) — runs entirely in your browser.</p>

      <!-- Drop zone -->
      <div class="drop-zone" id="drop-zone">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"/>
        </svg>
        <p id="drop-text">Drag &amp; drop your G-Code file here</p>
        <span class="file-name" id="file-name-label" hidden></span>
        <br />
        <span class="browse-link" id="browse-link">or browse to select</span>
        <input type="file" id="file-input" accept=".gcode,.gc,.g,.nc,.txt" hidden />
      </div>

      <!-- Parameters -->
      <div class="params-grid">
        <div class="param-group">
          <label for="x_translate">X Translate (mm)</label>
          <input type="number" id="x_translate" value="-1000" step="any" />
        </div>
        <div class="param-group">
          <label for="y_translate">Y Translate (mm)</label>
          <input type="number" id="y_translate" value="-1000" step="any" />
        </div>
        <div class="param-group">
          <label for="z_translate">Z Translate (mm)</label>
          <input type="number" id="z_translate" value="0" step="any" />
        </div>
      </div>

      <!-- Progress -->
      <div class="progress-wrap">
        <div class="progress-bar-bg">
          <div class="progress-bar-fill" id="progress-fill"></div>
        </div>
        <div class="progress-label" id="progress-label">0%</div>
      </div>

      <!-- Actions -->
      <div class="action-row">
        <button class="btn-start" id="start-btn" disabled>Start Processing</button>
        <a class="btn-download" id="download-btn" href="#" download>
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
          </svg>
          Download KRL Files
        </a>
      </div>

      <p class="status-msg" id="status-msg"></p>

    </section>
  </main>
  <footer></footer>

  <script src="inject-shared.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="kuka-processor.js"></script>
</body>
</html>
