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
            select.classList.remove("is-open");
            trigger.setAttribute("aria-expanded", "false");
            trigger
                .querySelector("svg")
                ?.setAttribute("data-lucide", "chevron-down");
            window.lucide?.createIcons();
        };

        trigger.addEventListener("click", () => {
            const opening = panel.hidden;
            panel.hidden = !opening;
            select.classList.toggle("is-open", opening);
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
                select.classList.add("is-open");
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

    document.querySelectorAll("[data-ui-password]").forEach((password) => {
        const input = password.querySelector("[data-ui-password-input]");
        const toggle = password.querySelector("[data-ui-password-toggle]");

        toggle.addEventListener("click", () => {
            const revealed = input.type === "password";
            input.type = revealed ? "text" : "password";
            toggle.setAttribute("aria-pressed", String(revealed));
            toggle.setAttribute(
                "aria-label",
                revealed ? "Скрыть пароль" : "Показать пароль",
            );
            toggle
                .querySelector("svg")
                ?.setAttribute("data-lucide", revealed ? "eye-off" : "eye");
            window.lucide?.createIcons();
        });
    });

    document.querySelectorAll("[data-ui-file-upload]").forEach((upload) => {
        const input = upload.querySelector("[data-ui-file-input]");
        const label = upload.querySelector("[data-ui-file-label]");

        input.addEventListener("change", () => {
            label.textContent = input.files[0]?.name ?? "Выберите файл";
        });

        ["dragenter", "dragover"].forEach((eventName) => {
            upload.addEventListener(eventName, (event) => {
                event.preventDefault();
                upload.classList.add("is-dragging");
            });
        });

        ["dragleave", "drop"].forEach((eventName) => {
            upload.addEventListener(eventName, () => {
                upload.classList.remove("is-dragging");
            });
        });
    });
});
