<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha de consulta RENIEC</title>
    <style>
        @page { margin: 28px 34px 36px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.45; }
        .header { min-height: 78px; padding-bottom: 14px; border-bottom: 3px solid #173f74; }
        .logo { float: left; width: 66px; height: 66px; object-fit: contain; }
        .institution { margin-left: 82px; padding-top: 8px; }
        .institution-name { margin: 0; color: #173f74; font-size: 17px; font-weight: bold; text-transform: uppercase; }
        .institution-subtitle { margin: 3px 0 0; color: #52627a; font-size: 10px; letter-spacing: .6px; text-transform: uppercase; }
        .document-meta { float: right; margin-top: -39px; color: #68778d; font-size: 9px; text-align: right; }
        .title-block { margin: 20px 0 16px; text-align: center; }
        .title-block h1 { margin: 0; color: #172033; font-size: 18px; }
        .title-block p { margin: 4px 0 0; color: #68778d; font-size: 10px; }
        .identity { width: 100%; padding: 16px; border: 1px solid #d7e0eb; border-left: 5px solid #173f74; border-collapse: separate; background: #f6f8fb; }
        .identity-photo-cell { width: 118px; vertical-align: middle; }
        .identity-data-cell { padding-left: 12px; vertical-align: middle; }
        .photo-wrap { width: 102px; height: 122px; border: 1px solid #c6d1df; background: #fff; text-align: center; }
        .photo { width: 100px; height: 120px; object-fit: cover; }
        .photo-empty { padding-top: 47px; color: #8b98a9; font-size: 9px; text-transform: uppercase; }
        .eyebrow { margin: 0 0 3px; color: #68778d; font-size: 9px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; }
        .full-name { margin: 0 0 14px; color: #173f74; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .dni-label { display: block; margin: 0 0 6px; color: #52627a; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .dni-number { display: inline-block; padding: 5px 10px; border-radius: 3px; background: #173f74; color: #fff; font-size: 15px; font-weight: bold; letter-spacing: 1px; }
        .section-title { margin: 20px 0 10px; padding-bottom: 6px; border-bottom: 1px solid #d7e0eb; color: #173f74; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .details { width: 100%; border: 1px solid #d7e0eb; border-collapse: collapse; }
        .details th, .details td { padding: 9px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .details tr:last-child th, .details tr:last-child td { border-bottom: 0; }
        .details th { width: 29%; background: #f6f8fb; color: #52627a; font-size: 8px; letter-spacing: .55px; text-transform: uppercase; }
        .details td { color: #172033; font-size: 11px; font-weight: bold; overflow-wrap: anywhere; }
        .notice { margin-top: 13px; padding: 9px 11px; border-left: 3px solid #d39b24; background: #fff8e7; color: #66501e; font-size: 9px; }
        .footer { position: fixed; right: 0; bottom: -20px; left: 0; padding-top: 8px; border-top: 1px solid #d7e0eb; color: #76859a; font-size: 8px; text-align: center; }
        .clear { clear: both; }
    </style>
</head>
<body>
    @php
        $fullName = trim(implode(' ', array_filter([
            $result['Nombres'] ?? null,
            $result['Apellido paterno'] ?? null,
            $result['Apellido materno'] ?? null,
        ])));
        $fields = [
            'Nombres' => $result['Nombres'] ?? null,
            'Apellido paterno' => $result['Apellido paterno'] ?? null,
            'Apellido materno' => $result['Apellido materno'] ?? null,
            'Estado civil' => $result['Estado civil'] ?? null,
            'Ubigeo' => $result['Ubigeo'] ?? null,
            'Restricción' => $result['Restricción'] ?? null,
            'Dirección' => $result['Dirección'] ?? null,
        ];
    @endphp

    <header class="header">
        @if($logo)<img class="logo" src="{{ $logo }}" alt="Escudo municipal">@endif
        <div class="institution">
            <p class="institution-name">Municipalidad Distrital de La Esperanza</p>
            <p class="institution-subtitle">Plataforma de Interoperabilidad del Estado Peruano</p>
        </div>
        <div class="document-meta">Documento generado<br>{{ now()->format('d/m/Y H:i') }}</div>
        <div class="clear"></div>
    </header>

    <section class="title-block">
        <h1>Ficha de consulta de identidad</h1>
        <p>Información obtenida mediante el servicio RENIEC de la Plataforma PIDE</p>
    </section>

    <table class="identity" role="presentation">
        <tr>
            <td class="identity-photo-cell">
                <div class="photo-wrap">
                    @if($photo)
                        <img class="photo" src="{{ $photo }}" alt="Fotografía del ciudadano">
                    @else
                        <div class="photo-empty">Sin fotografía disponible</div>
                    @endif
                </div>
            </td>
            <td class="identity-data-cell">
                <p class="eyebrow">Ciudadano consultado</p>
                <p class="full-name">{{ $fullName !== '' ? $fullName : 'Sin nombre registrado' }}</p>
                <span class="dni-label">Documento Nacional de Identidad</span>
                <span class="dni-number">{{ $result['DNI'] ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <h2 class="section-title">Datos registrados</h2>
    <table class="details">
        @foreach($fields as $label => $value)
            <tr>
                <th scope="row">{{ $label }}</th>
                <td>{{ filled($value) ? $value : '-' }}</td>
            </tr>
        @endforeach
    </table>

    <div class="notice">Este documento es informativo y refleja los datos entregados por RENIEC al momento de la consulta. No reemplaza un certificado oficial.</div>

    <footer class="footer">Sistema de Consultas PIDE · Municipalidad Distrital de La Esperanza</footer>
</body>
</html>
