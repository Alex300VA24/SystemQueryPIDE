# Arquitectura de inicio

```text
app/
├── Contracts/                 Puertos de casos de uso
├── Http/
│   ├── Controllers/           Entradas HTTP delgadas
│   └── Requests/              Validación HTTP reutilizable
├── Livewire/                  Estado e interacción del Dashboard SPA
├── Models/                    Entidades Eloquent
├── Services/                  Casos de uso transaccionales
└── Support/                   Navegación declarativa

resources/views/
├── components/                Iconos y controles compartidos
├── layouts/                   Shell autenticado e invitado
└── livewire/                  Secciones, tablas y modales
```

`DashboardController` solo compone navegación y vista. `Dashboard` cambia sección sin alterar URL. Componentes de usuarios y roles llaman contratos; implementaciones Eloquent concentran consultas y transacciones. Form Requests quedan listos para futuras entradas HTTP/API sin duplicar reglas de transporte.

## Centro de Ayuda

`Ayuda` arma las guías desde `DashboardNavigation::keysFor()` (solo se listan las secciones que el rol del usuario tiene asignadas) y las pasa a la vista como datos, no como HTML. La vista (`livewire/ayuda.blade.php`) renderiza cada guía como una tarjeta (`.ayuda-card`) dentro de `.ayuda-grid`; al hacer clic, Alpine.js abre un modal (`.modal-backdrop` + `.ayuda-modal-panel`) que muestra la guía completa (pasos, consejos y nota) leyendo el arreglo `guides` que se serializa una sola vez con `@js($groups)`. No hay ida y vuelta a Livewire para abrir el modal: todo el contenido ya está en el DOM/Alpine, así que la interacción es instantánea.

## Catálogo de íconos

`App\Models\Icono` (tabla `iconos`) guarda el catálogo de clases Font Awesome disponibles para los selectores de ícono de `GestionModulos`. Se siembra desde `database/seeders/data/icono.json`.
