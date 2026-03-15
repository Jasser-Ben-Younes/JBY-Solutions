document.addEventListener("DOMContentLoaded", () => {
  fetch("header.html")
    .then((response) => response.text())
    .then((headerHTML) => {
      document.querySelector("header").innerHTML = headerHTML;
    })
    .catch((err) => console.error("Failed to load header:", err));
});
