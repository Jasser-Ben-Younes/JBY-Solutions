document.addEventListener("DOMContentLoaded", () => {
  fetch("/header.html")
    .then(r => r.text())
    .then(html => {
      document.querySelector("header").innerHTML = html.trim();

      const toggle   = document.getElementById("nav-toggle");
      const navLinks = document.getElementById("nav-links");
      if (toggle && navLinks) {
        toggle.addEventListener("click", () => {
          const open = navLinks.classList.toggle("open");
          toggle.classList.toggle("open", open);
          toggle.setAttribute("aria-expanded", String(open));
        });
        navLinks.querySelectorAll("a").forEach(a =>
          a.addEventListener("click", () => {
            navLinks.classList.remove("open");
            toggle.classList.remove("open");
            toggle.setAttribute("aria-expanded", "false");
          })
        );
        document.addEventListener("click", e => {
          if (!e.target.closest(".navbar")) {
            navLinks.classList.remove("open");
            toggle.classList.remove("open");
            toggle.setAttribute("aria-expanded", "false");
          }
        });
      }
    })
    .catch(err => console.error("Failed to load header:", err));

  fetch("/footer.html")
    .then(r => r.text())
    .then(html => { document.querySelector("footer").innerHTML = html.trim(); })
    .catch(err => console.error("Failed to load footer:", err));
});
