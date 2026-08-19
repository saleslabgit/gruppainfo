document.addEventListener("DOMContentLoaded", () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    const button = document.querySelector("[data-stage-one-button]");
    const result = document.querySelector("[data-stage-one-result]");

    if (button && result) {
        button.addEventListener("click", () => {
            result.hidden = false;
        });
    }

    document.querySelectorAll("[data-ui-select]").forEach((select) => {
        const trigger = select.querySelector("[data-ui-select-trigger]");
        const panel = select.querySelector("[data-ui-select-panel]");
        const input = select.querySelector("[data-ui-select-value]");
        const label = select.querySelector("[data-ui-select-label]");
        const options = [...select.querySelectorAll('[role="option"]')];

        const close = () => {
            panel.hidden = true;
            trigger.setAttribute("aria-expanded", "false");
            trigger
                .querySelector("svg")
                ?.setAttribute("data-lucide", "chevron-down");
            window.lucide?.createIcons();
        };

        trigger.addEventListener("click", () => {
            const opening = panel.hidden;
            panel.hidden = !opening;
            trigger.setAttribute("aria-expanded", String(opening));
            trigger
                .querySelector("svg")
                ?.setAttribute(
                    "data-lucide",
                    opening ? "chevron-up" : "chevron-down",
                );
            window.lucide?.createIcons();
            if (opening) {
                options
                    .find(
                        (option) =>
                            option.getAttribute("aria-selected") === "true",
                    )
                    ?.focus() ?? options[0]?.focus();
            }
        });

        trigger.addEventListener("keydown", (event) => {
            if (["ArrowDown", "Enter", " "].includes(event.key)) {
                event.preventDefault();
                panel.hidden = false;
                trigger.setAttribute("aria-expanded", "true");
                trigger
                    .querySelector("svg")
                    ?.setAttribute("data-lucide", "chevron-up");
                window.lucide?.createIcons();
                options[0]?.focus();
            }
        });

        options.forEach((option, index) => {
            option.addEventListener("click", () => {
                input.value = option.dataset.value;
                label.textContent = option.querySelector("span").textContent;
                trigger.classList.remove("is-placeholder");
                options.forEach((item) => {
                    item.classList.toggle("is-selected", item === option);
                    item.setAttribute("aria-selected", String(item === option));
                });
                close();
                trigger.focus();
            });
            option.addEventListener("keydown", (event) => {
                if (event.key === "Escape") {
                    close();
                    trigger.focus();
                } else if (event.key === "ArrowDown") {
                    event.preventDefault();
                    options[(index + 1) % options.length].focus();
                } else if (event.key === "ArrowUp") {
                    event.preventDefault();
                    options[
                        (index - 1 + options.length) % options.length
                    ].focus();
                }
            });
        });

        document.addEventListener("click", (event) => {
            if (!select.contains(event.target)) {
                close();
            }
        });
    });
});
