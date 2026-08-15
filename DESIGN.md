# Sistema PIDE · identidad heredada

<!-- impeccable:design-schema 1 -->

## Dirección

Migración fiel de interfaz PHP original. Sidebar azul degradado, tarjetas blancas, fondos gris pizarra claro, sombras suaves y acentos RENIEC/SUNAT/SUNARP. Logos municipales y PIDE originales son autoridad visual.

## Tokens

- Fondo: `#f1f5f9`; superficie: `#ffffff`; tinta: `#0f172a`; texto secundario: `#64748b`.
- Primario: `#1e40af`; secundario: `#3b82f6`; sidebar profundo: `#1e293b`; foco: `#f0a202`.
- RENIEC: `#059669`; SUNAT: `#dc2626`; SUNARP: `#7c3aed`.
- Tipografía: Inter local/sistema, 16 px base.
- Radios: 12–16 px para superficies; píldoras solo en estados y controles pequeños.

## Componentes

Shell replica sidebar de 280 px y tarjetas del sistema original. SVG reemplaza dependencia masiva de iconos sin cambiar lenguaje visual. Modales comparten overlay, panel, cabecera, acciones y tratamiento responsive.

## Interacción

Dashboard conserva una URL. Livewire cambia componente activo dentro del panel, mantiene estados y anuncia carga. Movimiento respeta `prefers-reduced-motion`.

## Responsive

Sidebar desde 1024 px. Bajo ese ancho, barra superior y drawer. Grillas pasan a una columna; tablas usan scroll con encabezado visible o lista móvil. Ningún control táctil menor de 44 px.
