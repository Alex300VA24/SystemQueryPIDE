<?php

namespace App\Livewire;

use App\Support\DashboardNavigation;
use Livewire\Component;

final class Ayuda extends Component
{
    public function render(DashboardNavigation $navigation): \Illuminate\Contracts\View\View
    {
        $keys = $navigation->keysFor(auth()->user());

        $general = [
            $this->guide(
                'ayuda-ingreso',
                'chip-general',
                'fa-solid fa-right-to-bracket',
                'Ingreso al sistema',
                'Accede al Sistema de Consultas PIDE con las credenciales que la administración te asigna. Es el punto de entrada a todas las consultas y gestiones de tu rol.',
                [
                    'Abre el navegador (Chrome, Edge o Firefox) y escribe la dirección del sistema.',
                    'Escribe tu usuario y contraseña en el formulario de acceso.',
                    'Pulsa el botón Ingresar.',
                ],
                [
                    'Las credenciales son personales e intransferibles.',
                    'Tras varios intentos fallidos la cuenta se bloquea; contacta a la administración.',
                    'Si el sistema lo solicita, cambia tu contraseña antes de continuar.',
                ],
            ),
            $this->guide(
                'ayuda-navegacion',
                'chip-general',
                'fa-solid fa-list-ul',
                'Navegación por el menú',
                'El menú lateral muestra solo las opciones asignadas a tus roles. La sección que usas se mantiene resaltada para que siempre sepas dónde estás.',
                [
                    'Localiza la opción deseada en el menú lateral.',
                    'Si es un grupo (por ejemplo CONSULTAS), despliega el submenú y elige la consulta.',
                    'La sección activa queda resaltada; el título superior te confirma el lugar.',
                ],
                [
                    'Usa el botón superior para colapsar el menú y ganar espacio de lectura.',
                    'En pantallas pequeñas, abre el menú con el botón de las tres líneas.',
                ],
            ),
            $this->guide(
                'ayuda-sesion',
                'chip-general',
                'fa-solid fa-right-from-bracket',
                'Cierre de sesión',
                'Cierra la sesión al terminar tu jornada para proteger los datos institucionales.',
                [
                    'Pulsa el botón Cerrar Sesión en la parte inferior del menú.',
                    'Confirma en la ventana emergente.',
                ],
                [
                    'Cierra siempre la sesión al abandonar el equipo, aunque sea por poco tiempo.',
                ],
            ),
        ];

        if (in_array('password', $keys, true)) {
            $general[] = $this->guide(
                'ayuda-password',
                'chip-general',
                'fa-solid fa-key',
                'Cambio de contraseña',
                'Renueva tu contraseña cuando lo consideres necesario o cuando el sistema lo exija.',
                [
                    'Ve a SISTEMA → Actualizar Password.',
                    'Ingresa tu contraseña actual y la nueva.',
                    'Confirma y guarda los cambios.',
                ],
                [
                    'La nueva contraseña debe ser distinta a la anterior.',
                    'Guarda tu contraseña en un lugar seguro; no la compartas.',
                ],
                'Si tu cuenta exige cambio de contraseña, el sistema restringe la navegación hasta que completes este paso.'
            );
        }

        $groups = [];

        $groups[] = [
            'label' => 'Uso general',
            'icon' => 'fa-solid fa-circle-info',
            'items' => $general,
        ];

        $consultaGuides = [
            'dni' => $this->guide(
                'ayuda-dni',
                'chip-reniec',
                'fa-solid fa-id-card',
                'RENIEC · Consulta por DNI',
                'Valida la identidad de una persona a partir de su Documento Nacional de Identidad.',
                [
                    'Ve a CONSULTAS → RENIEC.',
                    'Escribe el número de DNI (8 dígitos) en el campo indicado.',
                    'Pulsa Consultar.',
                    'Revisa los datos de identidad: nombres, apellidos y estado del documento.',
                ],
                [
                    'El número de DNI tiene 8 dígitos; verifícalo antes de consultar.',
                    'Revisa primero un caso individual antes de hacer consultas repetidas.',
                ],
            ),
            'ruc' => $this->guide(
                'ayuda-ruc',
                'chip-sunat',
                'fa-solid fa-building-columns',
                'SUNAT · Consulta por RUC',
                'Obtén los datos registrales de una empresa a partir de su número de RUC.',
                [
                    'Ve a CONSULTAS → SUNAT.',
                    'Escribe el número de RUC (11 dígitos) en el campo indicado.',
                    'Pulsa Consultar.',
                    'Revisa la razón social, el estado del contribuyente y el domicilio fiscal.',
                ],
                [
                    'El RUC tiene 11 dígitos; verifica que el número esté completo.',
                ],
            ),
            'partidas' => $this->guide(
                'ayuda-partidas',
                'chip-sunarp',
                'fa-solid fa-file-signature',
                'SUNARP · Consulta de partidas',
                'Consulta la información registral de inmuebles, vehículos o personas jurídicas.',
                [
                    'Ve a CONSULTAS → SUNARP.',
                    'Ingresa el número de partida registral a consultar.',
                    'Pulsa Consultar.',
                    'Revisa la información de titulares y bienes registrados.',
                ],
                [
                    'Escribe la partida completa sin espacios para evitar errores.',
                ],
            ),
        ];

        $consultas = collect($consultaGuides)
            ->filter(fn (array $guide, string $key) => in_array($key, $keys, true));

        if ($consultas->isNotEmpty()) {
            $groups[] = [
                'label' => 'Consultas PIDE',
                'icon' => 'fa-solid fa-database',
                'items' => $consultas->values()->all(),
            ];
        }

        $adminGuides = [
            'usuarios' => $this->guide(
                'ayuda-usuarios',
                'chip-admin',
                'fa-solid fa-user-plus',
                'Registro de usuarios',
                'Crea y actualiza las cuentas del personal que usa el sistema, definiendo el rol que determina sus permisos.',
                [
                    'Ve a SISTEMA → Registrar Usuario.',
                    'Completa los datos personales y de acceso.',
                    'Asigna el rol correspondiente.',
                    'Guarda el registro.',
                ],
                [
                    'Asigna el rol de menor privilegio que el cargo requiera.',
                    'Usa el ícono de ojo para verificar la contraseña antes de guardar.',
                ],
                'Los roles definen qué módulos verá el usuario en su menú.'
            ),
            'roles' => $this->guide(
                'ayuda-roles',
                'chip-admin',
                'fa-solid fa-user-shield',
                'Gestión de roles',
                'Define roles con su nivel y los módulos que cada uno puede consultar o administrar.',
                [
                    'Ve a SISTEMA → Crear Roles.',
                    'Completa el código, el nombre y el nivel del rol.',
                    'Marca los módulos asignados; los grupos se marcan con un clic.',
                    'Guarda el rol.',
                ],
                [
                    'Un módulo marcado aparece en el menú de los usuarios con ese rol.',
                    'Solo se puede eliminar un rol sin usuarios asignados.',
                ],
                'Los cambios de módulos se reflejan en el menú de los usuarios al instante.'
            ),
            'modulos' => $this->guide(
                'ayuda-modulos',
                'chip-admin',
                'fa-solid fa-puzzle-piece',
                'Gestión de módulos',
                'Administra las opciones del menú: su jerarquía, su posición y su ícono.',
                [
                    'Ve a SISTEMA → Crear Módulos.',
                    'Define el nombre, el código, el nivel y el módulo padre.',
                    'Elige la posición dentro de su grupo y su ícono.',
                    'Guarda el módulo.',
                ],
                [
                    'Nivel 1 son opciones principales; el nivel 2 cuelga de una opción principal.',
                    'Un módulo de nivel 3 se muestra como pestaña dentro de su módulo padre (por ejemplo, las consultas de SUNAT).',
                    'Los módulos desactivados desaparecen del menú de todos.',
                ]
            ),
        ];

        $administracion = collect($adminGuides)
            ->filter(fn (array $guide, string $key) => in_array($key, $keys, true));

        if ($administracion->isNotEmpty()) {
            $groups[] = [
                'label' => 'Administración del sistema',
                'icon' => 'fa-solid fa-gear',
                'items' => $administracion->values()->all(),
            ];
        }

        return view('livewire.ayuda', ['groups' => $groups]);
    }

    private function guide(
        string $id,
        string $chip,
        string $icon,
        string $title,
        string $desc,
        array $steps,
        array $tips = [],
        ?string $note = null
    ): array {
        return compact('id', 'chip', 'icon', 'title', 'desc', 'steps', 'tips', 'note');
    }
}
