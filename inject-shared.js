document.addEventListener("DOMContentLoaded", () => {
  // Header
  fetch("header.html")
    .then((response) => response.text())
    .then((headerHTML) => {
      document.querySelector("header").innerHTML = headerHTML.trim();

      if (typeof initWaves === "function" && document.getElementById("waves")) {
        initWaves();
      }

      // Hamburger menu
      const toggle   = document.getElementById("nav-toggle");
      const navLinks = document.getElementById("nav-links");
      if (toggle && navLinks) {
        toggle.addEventListener("click", () => {
          const open = navLinks.classList.toggle("open");
          toggle.classList.toggle("open", open);
          toggle.setAttribute("aria-expanded", String(open));
        });

        // Close when any nav link is tapped
        navLinks.querySelectorAll("a").forEach(a =>
          a.addEventListener("click", () => {
            navLinks.classList.remove("open");
            toggle.classList.remove("open");
            toggle.setAttribute("aria-expanded", "false");
          })
        );

        // Close when tapping outside the navbar
        document.addEventListener("click", (e) => {
          if (!e.target.closest(".navbar")) {
            navLinks.classList.remove("open");
            toggle.classList.remove("open");
            toggle.setAttribute("aria-expanded", "false");
          }
        });
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
