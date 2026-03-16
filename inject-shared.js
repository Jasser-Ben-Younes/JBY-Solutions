document.addEventListener("DOMContentLoaded", () => {
  // Header
  fetch("header.html")
    .then((response) => response.text())
    .then((headerHTML) => {
      const headerEl = document.querySelector("header");
      headerEl.innerHTML = headerHTML.trim();

      // Start waves if function exists and canvas is present
      if (typeof initWaves === "function" && document.getElementById("waves")) {
        initWaves();
      }
    })
    .catch((err) => console.error("Failed to load header:", err));

  // Footer
  fetch("footer.html")
    .then((response) => response.text())
    .then((footerHTML) => {
      document.querySelector("footer").innerHTML = footerHTML.trim();
    })
    .catch((err) => console.error("Failed to load footer:", err));
});
