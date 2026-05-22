document.addEventListener('DOMContentLoaded', () => {
    const preferenceToggle = document.querySelector('[data-preferences-toggle]');
    const preferencePanel = document.querySelector('[data-preferences-panel]');

    if (!preferenceToggle || !preferencePanel) {
        return;
    }

    const operatorSelect = preferencePanel.querySelector('[data-operadoras-select]');
    const operatorWarning = preferencePanel.querySelector('[data-operadoras-warning]');
    const preferenceInputs = preferencePanel.querySelectorAll('input');

    const clearPreferences = () => {
        if (operatorSelect) {
            Array.from(operatorSelect.options).forEach((option) => {
                option.selected = false;
            });
        }

        preferenceInputs.forEach((input) => {
            input.value = '';
        });

        if (operatorWarning) {
            operatorWarning.hidden = true;
        }
    };

    const syncPreferences = () => {
        const shouldShow = preferenceToggle.value === 'sim';
        preferencePanel.hidden = !shouldShow;

        if (!shouldShow) {
            clearPreferences();
        }
    };

    if (operatorSelect) {
        operatorSelect.addEventListener('change', () => {
            const selected = Array.from(operatorSelect.selectedOptions);

            if (selected.length > 3) {
                selected[selected.length - 1].selected = false;
                if (operatorWarning) {
                    operatorWarning.hidden = false;
                }
                return;
            }

            if (operatorWarning) {
                operatorWarning.hidden = true;
            }
        });
    }

    preferenceToggle.addEventListener('change', syncPreferences);
    syncPreferences();
});
