<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $titulo }}</title>
        <style>
            * { box-sizing: border-box; }
            html, body {
                margin: 0;
                padding: 0;
                background: rgba(15, 23, 42, .55);
                font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            }
            .pide-swal-popup {
                max-width: 32rem;
                margin: 4rem auto;
                background: #ffffff;
                border-radius: 1.25rem;
                padding: 2rem;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .25);
                text-align: center;
            }
            .icono {
                width: 4.5rem;
                height: 4.5rem;
                margin: 0 auto 1rem;
                border-radius: 9999px;
                border: 3px solid #ef4444;
                color: #ef4444;
                font-size: 2.25rem;
                line-height: 1;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .pide-swal-title {
                font-size: 1.35rem;
                font-weight: 700;
                color: #1e293b;
                margin: 0 0 1rem;
            }
            .mensaje { color: #64748b; font-size: .95rem; line-height: 1.6; margin: 0 0 .75rem; word-break: break-word; }
            .ubicacion { color: #94a3b8; font-size: .8rem; line-height: 1.5; margin: 0 0 .75rem; word-break: break-all; }
            .solucion-titulo { color: #334155; font-size: .85rem; line-height: 1.6; font-weight: 600; margin: 0 0 .25rem; }
            .solucion { color: #64748b; font-size: .85rem; line-height: 1.6; margin: 0 0 1.5rem; }
            .pide-swal-confirm {
                display: inline-block;
                border: 0;
                border-radius: .75rem;
                padding: .65rem 2rem;
                font-weight: 600;
                font-size: .9rem;
                color: #ffffff;
                background: #ef4444;
                box-shadow: 0 4px 14px 0 rgba(0, 0, 0, .15);
                text-decoration: none;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <div class="pide-swal-popup">
            <div class="icono">&times;</div>
            <p class="pide-swal-title">{{ $titulo }}</p>

            @if ($debug)
                <p class="mensaje">{{ $mensaje }}</p>
                <p class="ubicacion">{{ $archivo }}:{{ $linea }}</p>
                <p class="solucion-titulo">Cómo solucionarlo:</p>
                <p class="solucion">{{ $solucion }}</p>
            @else
                <p class="mensaje">Ocurrió un error inesperado. Intenta nuevamente en unos momentos.</p>
            @endif

            <a class="pide-swal-confirm" href="{{ url('/pide/inicio') }}">Ir al inicio</a>
        </div>
    </body>
</html>
