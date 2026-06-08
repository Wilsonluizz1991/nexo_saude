import ApexCharts from 'apexcharts';
import { createIcons, icons } from 'lucide';

window.ApexCharts = ApexCharts;
window.lucide = { createIcons: () => createIcons({ icons }) };

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    const sidebarOpenButtons = document.querySelectorAll('[data-sidebar-open]');
    const sidebarCloseTargets = document.querySelectorAll('[data-sidebar-close]');
    const closeSidebar = () => document.body.classList.remove('nexo-sidebar-open');
    const openSidebar = () => document.body.classList.add('nexo-sidebar-open');

    sidebarOpenButtons.forEach((button) => button.addEventListener('click', openSidebar));
    sidebarCloseTargets.forEach((target) => target.addEventListener('click', closeSidebar));

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    const globalSearchForm = document.querySelector('[data-global-search-form]');

    if (globalSearchForm) {
        const input = globalSearchForm.querySelector('[data-global-search-input]');
        const searchUrl = globalSearchForm.getAttribute('action');
        let timeout = null;
        let lastSubmittedValue = input ? input.value.trim() : '';

        const isSearchPage = () => window.location.pathname.replace(/\/$/, '') === new URL(searchUrl, window.location.origin).pathname.replace(/\/$/, '');

        const redirectToSearch = (value) => {
            const normalizedValue = value.trim();

            if (normalizedValue === lastSubmittedValue && isSearchPage()) {
                return;
            }

            lastSubmittedValue = normalizedValue;

            if (normalizedValue.length === 0) {
                window.location.href = searchUrl;
                return;
            }

            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('q', normalizedValue);
            window.location.href = url.toString();
        };

        if (input && searchUrl) {
            input.addEventListener('input', () => {
                const value = input.value.trim();
                window.clearTimeout(timeout);

                timeout = window.setTimeout(() => {
                    if (value.length >= 3) {
                        redirectToSearch(value);
                        return;
                    }

                    if (value.length === 0 && isSearchPage()) {
                        redirectToSearch('');
                    }
                }, 500);
            });

            globalSearchForm.addEventListener('submit', (event) => {
                const value = input.value.trim();

                if (value.length >= 3) {
                    return;
                }

                event.preventDefault();

                if (value.length === 0) {
                    redirectToSearch('');
                }
            });
        }
    }

    const normalizarTextoPlano = (value) => String(value || '').toLowerCase();
    const isIndividual = (value) => normalizarTextoPlano(value).includes('individual');

    const syncIndividualLives = (root = document) => {
        const planFields = root.querySelectorAll('[data-plan-type]');

        planFields.forEach((planField) => {
            const group = planField.closest('form') || root;
            const livesField = group.querySelector('[data-lives-count]');

            if (!livesField) {
                return;
            }

            const apply = () => {
                if (isIndividual(planField.value)) {
                    livesField.value = '1';
                    livesField.readOnly = true;
                    livesField.classList.add('is-readonly');
                    livesField.setAttribute('aria-readonly', 'true');
                } else {
                    livesField.readOnly = false;
                    livesField.classList.remove('is-readonly');
                    livesField.removeAttribute('aria-readonly');
                }
            };

            planField.addEventListener('change', apply);
            planField.addEventListener('input', apply);
            apply();
        });
    };

    syncIndividualLives();

    document.querySelectorAll('[data-bootstrap-select-wrapper]').forEach((wrapper) => {
        const select = wrapper.previousElementSibling?.matches('select')
            ? wrapper.previousElementSibling
            : wrapper.parentElement?.querySelector('select');
        const label = wrapper.querySelector('[data-bootstrap-select-label]');

        if (!select || !label || wrapper.dataset.nexoSelectReady === '1') {
            return;
        }

        wrapper.dataset.nexoSelectReady = '1';

        const syncLabel = () => {
            const selected = select.options[select.selectedIndex];
            label.textContent = selected?.textContent?.trim() || 'Selecione';
            wrapper.querySelectorAll('[data-select-value]').forEach((item) => {
                item.classList.toggle('active', item.dataset.selectValue === select.value);
            });
        };

        wrapper.querySelectorAll('[data-select-value]').forEach((item) => {
            item.addEventListener('click', () => {
                select.value = item.dataset.selectValue;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                syncLabel();
            });
        });

        select.addEventListener('change', syncLabel);
        syncLabel();
    });

    document.querySelectorAll('[data-nexo-page-toast]').forEach((toast) => {
        let removed = false;
        let timeoutId = null;

        const close = () => {
            if (removed || !toast.isConnected) {
                return;
            }

            removed = true;
            if (timeoutId) {
                window.clearTimeout(timeoutId);
            }

            toast.classList.add('is-leaving');
            toast.addEventListener('animationend', () => toast.remove(), { once: true });
            window.setTimeout(() => toast.remove(), 360);
        };

        toast.querySelector('[data-toast-close]')?.addEventListener('click', close, { once: true });
        timeoutId = window.setTimeout(close, 4500);
    });

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
            if (['checkbox', 'radio'].includes(input.type)) {
                input.checked = false;

                return;
            }

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
