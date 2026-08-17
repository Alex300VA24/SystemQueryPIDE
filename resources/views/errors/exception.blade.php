<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $titulo }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-ink">
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                @if ($debug)
                    var detalle = '<p style="color:#64748b;font-size:.95rem;line-height:1.6;margin-bottom:.75rem;">{{ addslashes($mensaje) }}</p>'
                        + '<p style="color:#94a3b8;font-size:.8rem;line-height:1.5;margin-bottom:.75rem;">{{ addslashes($archivo) }}:{{ $linea }}</p>'
                        + '<p style="color:#334155;font-size:.85rem;line-height:1.6;font-weight:600;">Cómo solucionarlo:</p>'
                        + '<p style="color:#64748b;font-size:.85rem;line-height:1.6;">{{ addslashes($solucion) }}</p>';
                @else
                    var detalle = '<p style="color:#64748b;font-size:.95rem;line-height:1.6;">Ocurrió un error inesperado. Intenta nuevamente en unos momentos.</p>';
                @endif

                Swal.fire({
                    icon: 'error',
                    iconColor: '#ef4444',
                    title: @json($titulo),
                    html: detalle,
                    confirmButtonText: 'Ir al inicio',
                    confirmButtonColor: '#ef4444',
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
