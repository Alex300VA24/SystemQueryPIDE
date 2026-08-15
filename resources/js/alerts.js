import Swal from 'sweetalert2';

const ALERT_CONFIG = {
    success: { icon: 'success', color: '#10b981', title: 'Éxito' },
    info: { icon: 'info', color: '#3b82f6', title: 'Información' },
    warning: { icon: 'warning', color: '#f59e0b', title: 'Atención' },
    danger: { icon: 'error', color: '#ef4444', title: 'Error' },
};

function pideAlert(message, type = 'info', title = null) {
    if (!message) return;

    window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });

    const config = ALERT_CONFIG[type] || ALERT_CONFIG.info;

    return Swal.fire({
        title: title || config.title,
        html: `<p style="color:#64748b;font-size:.95rem;line-height:1.6;">${message}</p>`,
        icon: config.icon,
        iconColor: config.color,
        confirmButtonText: 'Entendido',
        confirmButtonColor: config.color,
        background: '#ffffff',
        backdrop: 'rgba(15, 23, 42, .55)',
        customClass: {
            popup: 'pide-swal-popup',
            title: 'pide-swal-title',
            confirmButton: 'pide-swal-confirm',
        },
        didOpen: (popup) => {
            popup.style.borderRadius = '1.25rem';
            popup.style.padding = '2rem';
            popup.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, .25)';
            const swalTitle = popup.querySelector('.swal2-title');
            if (swalTitle) {
                swalTitle.style.fontSize = '1.35rem';
                swalTitle.style.fontWeight = '700';
                swalTitle.style.color = '#1e293b';
            }
            const btn = popup.querySelector('.swal2-confirm');
            if (btn) {
                btn.style.borderRadius = '.75rem';
                btn.style.padding = '.65rem 2rem';
                btn.style.fontWeight = '600';
                btn.style.fontSize = '.9rem';
                btn.style.boxShadow = '0 4px 14px 0 rgba(0, 0, 0, .15)';
            }
        },
    });
}

window.pideAlert = pideAlert;

window.addEventListener('pide-alert', (event) => {
    pideAlert(event.detail?.message, event.detail?.type, event.detail?.title);
});
