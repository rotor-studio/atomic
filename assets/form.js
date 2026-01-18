document.addEventListener("DOMContentLoaded", () => {
  const sections = Array.from(document.querySelectorAll("[data-section]"));

  sections.forEach((section) => {
    const toggle = section.querySelector("[data-toggle]");
    toggle?.addEventListener("click", () => {
      section.classList.toggle("collapsed");
    });
  });

  const watchers = [
    {
      selector: '[name="name"]',
      target: "plan",
    },
    {
      selector: '[name="two_minute_version"]',
      target: "recordatorio",
    },
    {
      selector: '[name="notification_enabled"]',
      target: "estado",
    },
  ];

  watchers.forEach(({ selector, target }) => {
    const input = document.querySelector(selector);
    const targetSection = document.querySelector(`[data-section="${target}"]`);
    if (!input || !targetSection) return;

    const openIfFilled = () => {
      if (
        (input.type === "checkbox" && input.checked) ||
        (input.type !== "checkbox" && input.value.trim().length > 0)
      ) {
        targetSection.classList.remove("collapsed");
      }
    };

    input.addEventListener("input", openIfFilled);
    input.addEventListener("change", openIfFilled);
  });
});
