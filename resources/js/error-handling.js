document.addEventListener('livewire:init', () => {
    window.Livewire.hook('request', ({ fail }) => {
        fail(({ status, content, preventDefault }) => {
            preventDefault();

            let message = 'Ocurrió un error inesperado. Intenta nuevamente en unos momentos.';
            let title = null;

            if (content) {
                try {
                    const data = JSON.parse(content);
                    if (data.message) message = data.message;
                    if (data.title) title = data.title;
                } catch {
                    // no JSON: mensaje genérico
                }
            }

            const alerta = window.pideAlert(message, status === 419 ? 'warning' : 'danger', title);

            if (status === 419 && alerta) {
                alerta.then(() => {
                    window.location.href = window.PIDE_LOGIN_URL || '/login';
                });
            }
        });
    });
});