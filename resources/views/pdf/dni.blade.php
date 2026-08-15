<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha RENIEC</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 16px; border-bottom: 2px solid #087f5b; padding-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td { padding: 6px 4px; border-bottom: 1px solid #e5e7eb; }
        td.label { font-weight: bold; width: 40%; }
    </style>
</head>
<body>
    <h1>Ficha de consulta RENIEC</h1>
    <table>
        <tr><td class="label">DNI</td><td>{{ $dni }}</td></tr>
        <tr><td class="label">Nombres</td><td>{{ $nombres }}</td></tr>
        <tr><td class="label">Apellido paterno</td><td>{{ $apellido_paterno }}</td></tr>
        <tr><td class="label">Apellido materno</td><td>{{ $apellido_materno }}</td></tr>
        <tr><td class="label">Estado civil</td><td>{{ $estado_civil }}</td></tr>
        <tr><td class="label">Dirección</td><td>{{ $direccion }}</td></tr>
    </table>
    <p style="margin-top: 24px; font-size: 10px; color: #6b7280;">Generado el {{ now()->format('d/m/Y H:i') }} · Sistema PIDE</p>
</body>
</html>
