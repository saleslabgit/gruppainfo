document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector('[data-stage-one-button]');
    const result = document.querySelector('[data-stage-one-result]');

    if (!button || !result) {
        return;
    }

    button.addEventListener('click', () => {
        result.hidden = false;
    });
});
