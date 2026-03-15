document.addEventListener("DOMContentLoaded", () => {
  fetch("footer.html")
    .then((response) => response.text())
    .then((footerHTML) => {
      document.querySelector("footer").innerHTML = footerHTML;
    })
    .catch((err) => console.error("Failed to load footer:", err));
});