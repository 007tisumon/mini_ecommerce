(() => {
    const body = document.body;
    const sidebar = document.querySelector('#adminSidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const closeTargets = document.querySelectorAll('[data-sidebar-close]');

    const setSidebarOpen = (isOpen) => {
        body.classList.toggle('sidebar-open', isOpen);
        toggle?.setAttribute('aria-expanded', String(isOpen));
    };

    toggle?.addEventListener('click', () => {
        setSidebarOpen(!body.classList.contains('sidebar-open'));
    });

    closeTargets.forEach((target) => {
        target.addEventListener('click', () => setSidebarOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSidebarOpen(false);
        }
    });

    const passwordToggle = document.querySelector('[data-password-toggle]');
    const passwordInput = document.querySelector('#password');
    const passwordLabel = document.querySelector('[data-password-toggle-label]');

    passwordToggle?.addEventListener('click', () => {
        if (!(passwordInput instanceof HTMLInputElement)) {
            return;
        }

        const isVisible = passwordInput.type === 'text';
        passwordInput.type = isVisible ? 'password' : 'text';
        passwordToggle.setAttribute('aria-pressed', String(!isVisible));
        passwordToggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');

        if (passwordLabel) {
            passwordLabel.textContent = isVisible ? 'Show' : 'Hide';
        }
    });
})();
