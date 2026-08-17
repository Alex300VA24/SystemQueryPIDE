<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sesión expirada</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-ink">
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    title: 'Sesión expirada',
                    html: '<p style="color:#64748b;font-size:.95rem;line-height:1.6;">Tu sesión ha caducado por inactividad. Vuelve a iniciar sesión para continuar.</p>',
                    confirmButtonText: 'Ir al login',
                    confirmButtonColor: '#f59e0b',
                    background: '#ffffff',
                    backdrop: 'rgba(15, 23, 42, .55)',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'pide-swal-popup',
                        title: 'pide-swal-title',
                        confirmButton: 'pide-swal-confirm',
                    },
                }).then(function () {
                    window.location.href = '{{ route('login') }}';
                });
            });
        </script>
    </body>
</html>
