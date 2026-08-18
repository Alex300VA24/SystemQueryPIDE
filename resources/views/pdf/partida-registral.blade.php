<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Partida registral {{ $numero }}</title>
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; }
        .page { width: 100%; height: 100%; display: table; page-break-after: always; text-align: center; }
        .page:last-child { page-break-after: auto; }
        .page-inner { display: table-cell; vertical-align: middle; }
        img { display: block; max-width: 100%; max-height: 100%; margin: 0 auto; }
    </style>
</head>
<body>
    @foreach($images as $image)
        <div class="page">
            <div class="page-inner">
                <img src="data:image/jpeg;base64,{{ $image['imagen_base64'] }}" alt="">
            </div>
        </div>
    @endforeach
</body>
</html>
