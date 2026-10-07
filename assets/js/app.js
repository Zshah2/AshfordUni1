(() => {
  const button = document.getElementById("mobileMenuButton");
  const menu = document.getElementById("mobileMenu");
  if (!button || !menu) return;

  const setOpen = (open) => {
    menu.classList.toggle("hidden", !open);
    button.setAttribute("aria-expanded", open ? "true" : "false");
    button.setAttribute("aria-label", open ? "Close menu" : "Open menu");
  };

  button.addEventListener("click", () => {
    const isOpen = button.getAttribute("aria-expanded") === "true";
    setOpen(!isOpen);
  });

  // Close menu when clicking a nav link
  menu.addEventListener("click", (e) => {
    const target = e.target;
    if (target && target.closest && target.closest("a")) setOpen(false);
  });

  // Close on Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") setOpen(false);
  });
})();

(() => {
  const modal = document.querySelector("[data-registration-modal]");
  if (!modal) return;

  const openButton = document.querySelector("[data-open-registration]");
  const closeButtons = modal.querySelectorAll("[data-close-registration]");
  const firstInput = modal.querySelector("input");
  let lastFocusedElement = null;

  const setOpen = (open) => {
    modal.classList.toggle("is-open", open);
    modal.setAttribute("aria-hidden", open ? "false" : "true");
    document.body.style.overflow = open ? "hidden" : "";

    if (open) {
      lastFocusedElement = document.activeElement;
      window.setTimeout(() => firstInput?.focus(), 50);
    } else if (lastFocusedElement) {
      lastFocusedElement.focus();
    }
  };

  openButton?.addEventListener("click", () => setOpen(true));
  closeButtons.forEach((button) => {
    button.addEventListener("click", () => setOpen(false));
  });

  modal.addEventListener("click", (event) => {
    if (event.target === modal) setOpen(false);
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("is-open")) {
      setOpen(false);
    }
  });

  if (modal.querySelector(".registration-modal__error")) {
    setOpen(true);
  }
})();

