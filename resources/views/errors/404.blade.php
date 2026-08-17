<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Página no encontrada</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-ink">
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'question',
                    iconColor: '#3b82f6',
                    title: 'Página no encontrada',
                    html: '<p style="color:#64748b;font-size:.95rem;line-height:1.6;">La página que buscas no existe o fue movida.</p>',
                    confirmButtonText: 'Ir al inicio',
                    confirmButtonColor: '#3b82f6',
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
                    window.location.href = '{{ url('/pide/inicio') }}';
                });
            });
        </script>
    </body>
</html>
