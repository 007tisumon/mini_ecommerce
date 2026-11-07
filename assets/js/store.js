(() => {
    const menuButton = document.querySelector('[data-store-menu-toggle]');
    const menu = document.querySelector('#storeNavLinks');

    menuButton?.addEventListener('click', () => {
        const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', String(!isOpen));
        menuButton.setAttribute('aria-label', isOpen ? 'Open menu' : 'Close menu');
        menu?.classList.toggle('is-open', !isOpen);
    });

    menu?.querySelectorAll('a, button').forEach((item) => {
        item.addEventListener('click', () => {
            menu.classList.remove('is-open');
            menuButton?.setAttribute('aria-expanded', 'false');
            menuButton?.setAttribute('aria-label', 'Open menu');
        });
    });

    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.store-alert')?.remove());
    });

    document.querySelectorAll('[data-store-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement?.querySelector('input');
            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.textContent = visible ? 'Show' : 'Hide';
            button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        });
    });

    document.querySelectorAll('[data-image-fallback]').forEach((image) => {
        image.addEventListener('error', () => {
            if (!(image instanceof HTMLImageElement) || image.dataset.fallbackApplied === 'true') {
                return;
            }

            image.dataset.fallbackApplied = 'true';
            if (image.dataset.fallbackSrc) {
                image.src = image.dataset.fallbackSrc;
            } else {
                image.hidden = true;
            }
        });
    });

    const paymentMethods = document.querySelectorAll('[data-payment-method]');
    const simulatedPayment = document.querySelector('[data-simulated-payment]');

    const updatePaymentOptions = () => {
        const selected = document.querySelector('[data-payment-method]:checked');
        const showSimulation = selected instanceof HTMLInputElement && selected.value !== 'cod';
        if (simulatedPayment instanceof HTMLElement) {
            simulatedPayment.hidden = !showSimulation;
        }
    };

    paymentMethods.forEach((method) => {
        method.addEventListener('change', updatePaymentOptions);
    });
})();
