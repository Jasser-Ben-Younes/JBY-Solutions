document.addEventListener("DOMContentLoaded", () => {
  console.log("JBY Solution site loaded");

  const contactForm = document.querySelector("#contact-form");
  if (contactForm) {
    contactForm.addEventListener("submit", (event) => {
      event.preventDefault();
      alert("Contact form submitted (placeholder). Backend coming soon!");
    });
  }
});

