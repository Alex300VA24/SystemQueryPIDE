# Manual de Livewire para SCPIDE

## 1. Verificación de la Instalación Global de Livewire

Livewire está configurado y funcionando globalmente en este proyecto. A continuación los puntos de verificación:

### 1.1 Dependencias instaladas
- **Livewire v3.4**: `livewire/livewire`
- **Livewire Volt v1.0**: `livewire/volt` (sintaxis de componente inline en Blade)

Verificado en: [composer.json](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/composer.json#L13-L14)

### 1.2 Configuración publicada
Archivo: [config/livewire.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/config/livewire.php)

Puntos clave de la configuración:
- `class_namespace` → `App\Livewire` (donde se buscan las clases PHP de componentes)
- `view_path` → `resources/views/livewire` (donde se guardan las vistas Blade)
- `layout` → `layouts.app` (layout por defecto para full-page components)
- `inject_assets` → `true` (Livewire inyecta CSS/JS automáticamente en `<head>` y antes de `</body>`)
- `pagination_theme` → `tailwind` (paginación con Tailwind CSS)

### 1.3 Configuración de Volt
El [VoltServiceProvider.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/app/Providers/VoltServiceProvider.php#L21-L27) registra dos rutas para buscar componentes Volt:
- `resources/views/livewire`
- `resources/views/pages`

### 1.4 Layouts con Livewire cargado
Los layouts principales ya incluyen todo lo necesario (Livewire inyecta sus assets automáticamente):
- [layouts/app.blade.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/resources/views/layouts/app.blade.php) → Layout para usuarios autenticados
- [layouts/guest.blade.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/resources/views/layouts/guest.blade.php) → Layout para páginas públicas (login, registro)

**Nota Importante**: En Livewire 3, `@livewireStyles` y `@livewireScripts` no son necesarios porque `inject_assets` está en `true`. Livewire detecta las etiquetas `<head>` y `</body>` e inyecta los assets automáticamente.

### 1.5 Comandos Artisan disponibles
Livewire está correctamente registrado (ejecuta `php artisan list livewire` para verificar):

```
  livewire:attribute        Crear clase de atributo Livewire
  livewire:form             Crear clase de formulario Livewire
  livewire:make             Crear nuevo componente Livewire
  livewire:copy             Copiar un componente
  livewire:delete           Eliminar un componente
  livewire:move             Mover un componente
  livewire:publish          Publicar configuración/stubs
  livewire:layout           Crear nuevo archivo de layout
```

---

## 2. Estructura de Carpetas Livewire en el Proyecto

```
app/
└── Livewire/
    ├── Actions/            (acciones invocables - ej: Logout.php)
    │   └── Logout.php
    └── Forms/              (Form Objects reutilizables)
        └── LoginForm.php

resources/
└── views/
    └── livewire/
        ├── layout/         (componentes de layout)
        │   └── navigation.blade.php   (Ejemplo Volt - Navegación)
        ├── pages/          (full-page components Volt)
        │   └── auth/
        │       ├── login.blade.php
        │       ├── register.blade.php
        │       └── ...
        ├── profile/        (componentes de perfil - Volt)
        │   ├── update-profile-information-form.blade.php
        │   ├── update-password-form.blade.php
        │   └── delete-user-form.blade.php
        └── welcome/
            └── navigation.blade.php
```

---

## 3. Dos Formas de Crear Componentes

Este proyecto usa **dos estilos** de Livewire. Elige según la complejidad:

### Opción A: Volt (Componentes Inline en Blade) → RECOMENDADO para la mayoría
Ideal para componentes de mediana complejidad, CRUDs sencillos y páginas completas. La **clase PHP y la vista Blade van en el mismo archivo**.

**Ubicación**: `resources/views/livewire/[nombre].blade.php` o `resources/views/pages/[nombre].blade.php`

**Patrón existente en el proyecto**:
- [update-profile-information-form.blade.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/resources/views/livewire/profile/update-profile-information-form.blade.php)
- [login.blade.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/resources/views/livewire/pages/auth/login.blade.php)

**Estructura básica**:

```blade
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;   // Si quieres especificar layout
use Livewire\Attributes\Rule;     // Para validaciones inline

new #[Layout('layouts.app')] class extends Component
{
    // ===== 1. PROPIEDADES PÚBLICAS (visibles en Blade) =====
    public string $nombre = '';
    public string $descripcion = '';

    // ===== 2. HOOKS (ciclo de vida) =====
    public function mount(): void
    {
        // Se ejecuta al inicializar el componente (1 sola vez)
        // $this->nombre = 'valor inicial';
    }

    // ===== 3. MÉTODOS PÚBLICOS (llamables desde Blade con wire:click) =====
    public function guardar()
    {
        // Lógica de negocio
    }

    // ===== 4. RENDER (opcional en Volt - es automático) =====
}; ?>

<!-- ===== 5. VISTA BLADE ===== -->
<div>
    <h2>Mi Componente</h2>
    <form wire:submit="guardar">
        <input type="text" wire:model="nombre">
        @error('nombre') <span>{{ $message }}</span> @enderror
        <button type="submit">Guardar</button>
    </form>
</div>
```

### Opción B: Clase PHP + Vista Blade separadas
Ideal para componentes MUY complejos, lógica pesada, o cuando quieres testear la clase PHP unitariamente.

**Ubicación**:
- Clase: `app/Livewire/[Nombre].php`
- Vista: `resources/views/livewire/[nombre].blade.php`

**Crear con Artisan**:
```bash
php artisan livewire:make NombreComponente
```

**Ejemplo de clase**:
```php
// app/Livewire/UsuariosCrud.php
namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class UsuariosCrud extends Component
{
    public $usuarios;
    public string $buscar = '';

    public function mount()
    {
        $this->usuarios = User::all();
    }

    public function eliminar(User $user)
    {
        $user->delete();
        session()->flash('mensaje', 'Usuario eliminado');
        return redirect()->route('usuarios');
    }

    public function render()
    {
        return view('livewire.usuarios-crud', [
            'usuarios' => User::where('name', 'like', "%{$this->buscar}%")->get(),
        ]);
    }
}
```

```blade
{{-- resources/views/livewire/usuarios-crud.blade.php --}}
<div>
    <input type="text" wire:model.live="buscar" placeholder="Buscar...">
    @foreach($usuarios as $u)
        <div>{{ $u->name }}
            <button wire:click="eliminar({{ $u->id }})">Borrar</button>
        </div>
    @endforeach
</div>
```

---

## 4. Crear un CRUD Completo usando Volt (Paso a Paso)

Vamos a crear un CRUD para el modelo **Rol** como ejemplo. Modelo disponible en: [Rol.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/app/Models/Rol.php)

Campos del modelo Rol:
- `id`, `codigo`, `nombre`, `descripcion`, `nivel`, `activo` (boolean), timestamps

### Paso 1: Crear el Archivo del Componente Volt

Crea el archivo: `resources/views/livewire/roles-crud.blade.php`

```blade
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use App\Models\Rol;

new #[Layout('layouts.app')] class extends Component
{
    // ====== CONFIGURACIÓN ======
    use WithPagination;   // Habilitar paginación

    public $tituloPagina = 'Gestión de Roles';

    // ====== ESTADO DEL CRUD ======
    public bool $mostrarModal = false;
    public bool $modoEdicion = false;
    public ?int $rolIdEditar = null;

    // ====== FILTROS / BÚSQUEDA ======
    public string $buscar = '';
    public string $filtroActivo = 'todos';   // 'todos', 'si', 'no'
    public int $porPagina = 10;

    // ====== CAMPOS DEL FORMULARIO + VALIDACIÓN ======
    #[Rule('required|string|max:20|unique:rols,codigo')]
    public string $codigo = '';

    #[Rule('required|string|max:100')]
    public string $nombre = '';

    #[Rule('nullable|string|max:255')]
    public string $descripcion = '';

    #[Rule('required|integer|min:0|max:100')]
    public int $nivel = 0;

    #[Rule('boolean')]
    public bool $activo = true;

    // ====== LISTENERS: Resetear paginación cuando cambian filtros ======
    public function updatedBuscar(): void { $this->resetPage(); }
    public function updatedFiltroActivo(): void { $this->resetPage(); }
    public function updatedPorPagina(): void { $this->resetPage(); }

    // ====== ABRIR MODAL CREAR ======
    public function abrirModalCrear(): void
    {
        $this->resetFormulario();
        $this->modoEdicion = false;
        $this->rolIdEditar = null;
        $this->mostrarModal = true;
    }

    // ====== ABRIR MODAL EDITAR ======
    public function abrirModalEditar(int $id): void
    {
        $rol = Rol::findOrFail($id);
        $this->rolIdEditar = $id;
        $this->codigo      = $rol->codigo;
        $this->nombre      = $rol->nombre;
        $this->descripcion = $rol->descripcion ?? '';
        $this->nivel       = $rol->nivel;
        $this->activo      = (bool) $rol->activo;
        $this->modoEdicion = true;
        $this->mostrarModal = true;
    }

    // ====== CERRAR MODAL ======
    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetFormulario();
        $this->resetValidation();
    }

    // ====== REINICIAR FORMULARIO ======
    private function resetFormulario(): void
    {
        $this->codigo = '';
        $this->nombre = '';
        $this->descripcion = '';
        $this->nivel = 0;
        $this->activo = true;
    }

    // ====== GUARDAR (CREAR o ACTUALIZAR) ======
    public function guardar(): void
    {
        // Regla condicional: en edición, unique ignora el propio registro
        if ($this->modoEdicion) {
            $this->validate([
                'codigo' => 'required|string|max:20|unique:rols,codigo,' . $this->rolIdEditar,
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:255',
                'nivel' => 'required|integer|min:0|max:100',
                'activo' => 'boolean',
            ]);
        } else {
            $this->validate();   // Usa las reglas #[Rule]
        }

        $datos = [
            'codigo'      => $this->codigo,
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion,
            'nivel'       => $this->nivel,
            'activo'      => $this->activo,
        ];

        if ($this->modoEdicion) {
            Rol::whereId($this->rolIdEditar)->update($datos);
            $this->dispatch('rol-actualizado', mensaje: 'Rol actualizado correctamente');
        } else {
            Rol::create($datos);
            $this->dispatch('rol-creado', mensaje: 'Rol creado correctamente');
        }

        $this->cerrarModal();
    }

    // ====== ELIMINAR ======
    public function eliminar(int $id): void
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();
        $this->dispatch('rol-eliminado', mensaje: 'Rol eliminado');
    }

    // ====== RENDER (con paginación y filtros) ======
    public function with(): array
    {
        $query = Rol::query()
            ->when($this->buscar, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('codigo', 'like', "%{$this->buscar}%")
                        ->orWhere('nombre', 'like', "%{$this->buscar}%")
                        ->orWhere('descripcion', 'like', "%{$this->buscar}%");
                });
            })
            ->when($this->filtroActivo === 'si', fn($q) => $q->where('activo', true))
            ->when($this->filtroActivo === 'no', fn($q) => $q->where('activo', false))
            ->orderBy('nivel', 'asc')
            ->orderBy('nombre', 'asc');

        return [
            'roles' => $query->paginate($this->porPagina),
        ];
    }
}; ?>

<div>
    <!-- ====== HEADER ====== -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $tituloPagina }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <!-- ====== BARRA DE BUSQUEDA / FILTROS ====== -->
                <div class="flex flex-wrap gap-4 mb-6 items-center justify-between">
                    <div class="flex flex-wrap gap-3 items-center">
                        <x-text-input
                            wire:model.live.debounce.300ms="buscar"
                            type="text"
                            placeholder="Buscar por código, nombre o descripción..."
                            class="w-80"
                        />

                        <select wire:model.live="filtroActivo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="todos">Todos los estados</option>
                            <option value="si">Solo activos</option>
                            <option value="no">Solo inactivos</option>
                        </select>

                        <select wire:model.live="porPagina" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="10">10 por pág.</option>
                            <option value="25">25 por pág.</option>
                            <option value="50">50 por pág.</option>
                            <option value="100">100 por pág.</option>
                        </select>
                    </div>

                    <x-primary-button wire:click="abrirModalCrear">
                        + Nuevo Rol
                    </x-primary-button>
                </div>

                <!-- ====== TABLA ====== -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Activo</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($roles as $rol)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $rol->codigo }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $rol->nombre }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $rol->descripcion ?? '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $rol->nivel }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                        @if ($rol->activo)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Sí</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-medium space-x-2">
                                        <button wire:click="abrirModalEditar({{ $rol->id }})"
                                                class="text-indigo-600 hover:text-indigo-900">
                                            Editar
                                        </button>
                                        <button wire:click="eliminar({{ $rol->id }})"
                                                wire:confirm="¿Estás seguro de eliminar el rol '{{ $rol->nombre }}'?"
                                                class="text-red-600 hover:text-red-900">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No se encontraron registros.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ====== PAGINACIÓN ====== -->
                <div class="mt-4">
                    {{ $roles->links() }}
                </div>

            </div>
        </div>
    </div>

    <!-- ====== MODAL CREAR/EDITAR (usando componente modal del proyecto) ====== -->
    <x-modal wire:model.live="mostrarModal" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                {{ $modoEdicion ? 'Editar Rol' : 'Nuevo Rol' }}
            </h2>

            <form wire:submit="guardar" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="codigo" value="Código" />
                        <x-text-input wire:model="codigo" id="codigo" type="text" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('codigo')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nivel" value="Nivel" />
                        <x-text-input wire:model="nivel" id="nivel" type="number" min="0" max="100" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('nivel')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input wire:model="nombre" id="nombre" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea wire:model="descripcion" id="descripcion" rows="3"
                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="flex items-center gap-2">
                    <input wire:model="activo" id="activo" type="checkbox"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <x-input-label for="activo" value="Activo" class="!mt-0" />
                </div>

                <!-- ====== BOTONES MODAL (siguiendo preferencia del usuario) ====== -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <x-secondary-button wire:click="cerrarModal">
                        Cerrar
                    </x-secondary-button>
                    <x-primary-button type="submit">
                        {{ $modoEdicion ? 'Actualizar' : 'Guardar' }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
```

### Paso 2: Agregar la Ruta

Edita [routes/web.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/routes/web.php) y agrega:

```php
// CRUD de Roles usando Livewire Volt Full-Page Component
Route::get('roles', function () {
    return view('livewire.roles-crud');
})
->middleware(['auth', 'verified'])
->name('roles');
```

**O** (más limpio) — si prefieres usar una clase PHP separada:
```php
use App\Livewire\RolesCrud;
Route::get('roles', RolesCrud::class)->middleware(['auth', 'verified'])->name('roles');
```

---

## 5. Consultas Grandes / Reportes (Mejores Prácticas)

Cuando manejes tablas con **miles de registros**, aplica estas técnicas para mantener el rendimiento:

### 5.1 Técnicas de Optimización

#### ✅ Usa Paginación (NUNCA `->get()` directamente)
```php
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    public int $porPagina = 25;

    public function with(): array
    {
        return [
            'registros' => MiModelo::query()
                ->select('id', 'campo1', 'campo2')   // Selecciona SOLO los campos necesarios
                ->where(...)
                ->orderBy('id', 'desc')
                ->paginate($this->porPagina),   // ✅ paginate(), NO get()
        ];
    }
}
```

#### ✅ Selecciona solo columnas necesarias
Evita `SELECT *`:
```php
->select('id', 'nombre', 'codigo', 'created_at')
```

#### ✅ Eager Loading para relaciones (evita N+1)
```php
// ❌ MAL: 1 query + N queries por relación
$sistemas = Sistema::paginate(25); // foreach echo $sistema->modulos->count() → 26 queries

// ✅ BIEN: 2 queries totales
$sistemas = Sistema::with('modulos:id,sistema_id,nombre')  // especifica columnas FK + nombre
    ->paginate(25);
```

#### ✅ Debounce en búsqueda (evita requests por cada tecla)
```blade
{{-- Espera 300ms después de que el usuario deja de escribir --}}
<x-text-input wire:model.live.debounce.300ms="buscar" ... />
```

#### ✅ `wire:key` en loops con muchos items
```blade
@foreach ($registros as $reg)
    <tr wire:key="reg-{{ $reg->id }}">
        ...
    </tr>
@endforeach
```

#### ✅ Campos computed en lugar de hacer cálculos por fila
```blade
{{-- ❌ Evita esto en cada fila: --}}
<td>{{ $reg->items->sum('precio') }}</td>

{{-- ✅ Mejor hacer el cálculo en la consulta SQL: --}}
->withSum('items as total_items', 'precio')
```

#### ✅ Lazy Loading para componentes que tardan
Envuelve el componente en una página usando el atributo `lazy`:
```blade
{{-- En otra vista, incrusta el componente con carga lazy --}}
<livewire:reporte-pesado lazy />
```

O en el propio componente Volt:
```php
use Livewire\Attributes\Lazy;

new #[Lazy] #[Layout('layouts.app')] class extends Component { ... }
```

### 5.2 Ejemplo: Reporte / Consulta Grande

Archivo: `resources/views/livewire/reporte-sistemas.blade.php`

```blade
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Sistema;
use App\Models\Modulo;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $ordenarPor = 'nombre';
    public string $ordenDir = 'asc';
    public ?bool $soloActivos = null;
    public int $porPagina = 25;

    // Reset paginación al cambiar filtros
    public function updated($prop): void
    {
        if (in_array($prop, ['buscar', 'soloActivos', 'ordenarPor', 'ordenDir', 'porPagina'])) {
            $this->resetPage();
        }
    }

    // Cambiar ordenamiento al hacer click en encabezado
    public function ordenar(string $columna): void
    {
        if ($this->ordenarPor === $columna) {
            $this->ordenDir = $this->ordenDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $columna;
            $this->ordenDir = 'asc';
        }
    }

    public function with(): array
    {
        // Columnas permitidas para ordenamiento (evita SQL injection)
        $colsPermitidas = ['id', 'codigo', 'nombre', 'version', 'orden', 'created_at'];
        $colOrden = in_array($this->ordenarPor, $colsPermitidas) ? $this->ordenarPor : 'nombre';

        $query = Sistema::query()
            ->select('id', 'codigo', 'nombre', 'descripcion', 'url', 'icono', 'version', 'orden', 'activo', 'created_at')
            // Conteo de módulos (1 solo query, no N+1)
            ->withCount(['modulos as total_modulos'])

            ->when($this->buscar, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('codigo', 'like', "%{$this->buscar}%")
                       ->orWhere('nombre', 'like', "%{$this->buscar}%")
                       ->orWhere('descripcion', 'like', "%{$this->buscar}%");
                });
            })

            ->when($this->soloActivos === true,  fn($q) => $q->where('activo', true))
            ->when($this->soloActivos === false, fn($q) => $q->where('activo', false))

            ->orderBy($colOrden, $this->ordenDir);

        return [
            'sistemas' => $query->paginate($this->porPagina),
        ];
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Reporte de Sistemas</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-lg">

                <!-- ====== FILTROS ====== -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="md:col-span-2">
                        <x-text-input wire:model.live.debounce.500ms="buscar"
                                      placeholder="Buscar sistema..."
                                      class="w-full" />
                    </div>

                    <select wire:model.live="soloActivos" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">Todos (activos e inactivos)</option>
                        <option value="1">Solo activos</option>
                        <option value="0">Solo inactivos</option>
                    </select>

                    <select wire:model.live="porPagina" class="border-gray-300 rounded-md shadow-sm">
                        <option value="25">25 / pág</option>
                        <option value="50">50 / pág</option>
                        <option value="100">100 / pág</option>
                    </select>
                </div>

                <!-- ====== TABLA ====== -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @php
                                    $cols = [
                                        ['key' => 'id',       'label' => 'ID'],
                                        ['key' => 'codigo',   'label' => 'Código'],
                                        ['key' => 'nombre',   'label' => 'Nombre'],
                                        ['key' => 'version',  'label' => 'Versión'],
                                        ['key' => null,       'label' => 'Módulos'],
                                        ['key' => 'orden',    'label' => 'Orden'],
                                        ['key' => null,       'label' => 'Estado'],
                                        ['key' => 'created_at','label' => 'Creado'],
                                    ];
                                @endphp
                                @foreach($cols as $c)
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider {{ $c['key'] ? 'cursor-pointer select-none hover:text-indigo-600' : '' }}"
                                        @if($c['key']) wire:click="ordenar('{{ $c['key'] }}')" @endif>
                                        <span class="inline-flex items-center gap-1">
                                            {{ $c['label'] }}
                                            @if($ordenarPor === $c['key'])
                                                <span class="text-indigo-600">
                                                    {{ $ordenDir === 'asc' ? '↑' : '↓' }}
                                                </span>
                                            @endif
                                        </span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($sistemas as $s)
                                <tr wire:key="sis-{{ $s->id }}" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $s->id }}</td>
                                    <td class="px-4 py-2 text-sm font-medium">{{ $s->codigo }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ $s->url }}" target="_blank" rel="noopener"
                                           class="text-indigo-600 hover:underline">
                                            {{ $s->nombre }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $s->version ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-center font-semibold">{{ $s->total_modulos }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $s->orden }}</td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $s->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $s->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-500">
                                        {{ $s->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                        No hay sistemas que coincidan con los filtros.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ====== PAGINACIÓN + RESUMEN ====== -->
                <div class="mt-6 flex items-center justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        Mostrando {{ $sistemas->firstItem() }} - {{ $sistemas->lastItem() }}
                        de <b>{{ $sistemas->total() }}</b> registros
                    </div>
                    <div>{{ $sistemas->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 6. Uso de Form Objects (Reutilización de Validación y Lógica)

Cuando un formulario se usa en múltiples lugares (ej: crear/editar Rol), extrae la lógica a un **Form Object**.

### Crear un Form
```bash
php artisan livewire:form RolForm
```

Esto crea: `app/Livewire/Forms/RolForm.php`

**Contenido ejemplo**:
```php
<?php

namespace App\Livewire\Forms;

use App\Models\Rol;
use Livewire\Attributes\Rule;
use Livewire\Form;

class RolForm extends Form
{
    public ?Rol $modelo = null;

    #[Rule('required|string|max:20')]
    public string $codigo = '';

    #[Rule('required|string|max:100')]
    public string $nombre = '';

    #[Rule('nullable|string|max:255')]
    public string $descripcion = '';

    #[Rule('required|integer|min:0|max:100')]
    public int $nivel = 0;

    #[Rule('boolean')]
    public bool $activo = true;

    // Cargar datos del modelo al formulario
    public function setModel(Rol $rol): void
    {
        $this->modelo = $rol;
        $this->codigo      = $rol->codigo;
        $this->nombre      = $rol->nombre;
        $this->descripcion = $rol->descripcion ?? '';
        $this->nivel       = $rol->nivel;
        $this->activo      = (bool) $rol->activo;
    }

    // Guardar
    public function guardar(): Rol
    {
        // Validación condicional para unique
        $reglas = $this->getRules();
        if ($this->modelo) {
            $reglas['codigo'] = 'required|string|max:20|unique:rols,codigo,' . $this->modelo->id;
        }
        $this->validate($reglas);

        $datos = $this->only(['codigo','nombre','descripcion','nivel','activo']);

        if ($this->modelo) {
            $this->modelo->update($datos);
            return $this->modelo->fresh();
        }
        return Rol::create($datos);
    }
}
```

**Usar el Form en un componente Volt**:
```blade
<?php

use App\Livewire\Forms\RolForm;
use App\Models\Rol;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    public RolForm $form;   // ← Inyecta el Form Object

    public function editar(Rol $rol): void
    {
        $this->form->setModel($rol);
    }

    public function guardar(): void
    {
        $this->form->guardar();    // ← Toda la validación + save
        $this->form->reset();      // ← Limpiar formulario
    }
}; ?>

<div>
    <form wire:submit="guardar">
        {{-- IMPORTANTE: usa wire:model="form.codigo" (nombre del form . propiedad) --}}
        <x-text-input wire:model="form.codigo" />
        <x-input-error :messages="$errors->get('form.codigo')" />

        <x-text-input wire:model="form.nombre" />
        <x-input-error :messages="$errors->get('form.nombre')" />

        <x-primary-button>Guardar</x-primary-button>
    </form>
</div>
```

---

## 7. Comunicación entre Componentes (Eventos / Dispatch)

### Emitir un evento
```php
// Desde un componente
$this->dispatch('usuario-actualizado', id: $user->id, nombre: $user->name);
```

### Escuchar evento (desde otro componente)
```php
// Opción 1: Con atributo (Livewire 3)
use Livewire\Attributes\On;

new class extends Component
{
    #[On('usuario-actualizado')]
    public function refrescarListaUsuario($id, $nombre)
    {
        // Lógica al recibir el evento
    }
}
```

```blade
{{-- Opción 2: Escuchar en Blade con x-on --}}
<div x-on:usuario-actualizado.window="alert('Usuario actualizado')">
    ...
</div>

{{-- El proyecto ya usa esto en la navegación:
     x-on:profile-updated.window="name = $event.detail.name" --}}
```

---

## 8. Incrustar Componentes en Vistas Blade

### Componente Volt o Livewire
```blade
{{-- Básico --}}
<livewire:profile.update-password-form />

{{-- Con props (parámetros) --}}
<livewire:roles-crud :tituloPagina="'Gestión de Roles'" />

{{-- Con carga diferida (lazy) --}}
<livewire:reporte-pesado lazy />

{{-- Con wire:key (requerido en loops) --}}
@foreach($lista as $item)
    <livewire:item-card :item="$item" :key="'item-'.$item->id" />
@endforeach
```

### Componente inline (dentro de una vista Blade tradicional)
También puedes agregar `<livewire:...>` en cualquier Blade, por ejemplo en:
- [dashboard.blade.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/resources/views/dashboard.blade.php)
- [profile.blade.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/resources/views/profile.blade.php)

---

## 9. Resumen de Directivas `wire:` más usadas

| Directiva | Uso | Ejemplo |
|---|---|---|
| `wire:model` | Binding bidireccional con propiedad pública | `<input wire:model="nombre">` |
| `wire:model.live` | Actualiza en cada cambio (Livewire 3) | `<input wire:model.live="buscar">` |
| `wire:model.live.debounce.300ms` | Actualiza 300ms después del último cambio | Buscadores |
| `wire:submit` | Método al enviar formulario | `<form wire:submit="guardar">` |
| `wire:click` | Método al hacer click | `<button wire:click="eliminar({{ $id }})">` |
| `wire:confirm` | Confirmación JS antes de ejecutar | `wire:confirm="¿Seguro?" wire:click="eliminar"` |
| `wire:navigate` | Navegación SPA entre páginas | `<a href="/roles" wire:navigate>` |
| `wire:model.live` en modal | Sincroniza el estado | `<x-modal wire:model.live="abierto">` |
| `wire:key` | Key en items de listas | `<tr wire:key="f-{{$fila->id}}">` |

---

## 10. Modelos Disponibles en el Proyecto (para usarlos en CRUDs)

| Modelo | Tabla | Campos principales |
|---|---|---|
| [Estado.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/app/Models/Estado.php) | `estado` | estado_codigo, estado_descripcion, estado_aplicable_a |
| [Sistema.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/app/Models/Sistema.php) | `sistemas` | codigo, nombre, descripcion, url, icono, version, orden, activo |
| [Modulo.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/app/Models/Modulo.php) | `modulos` | sistema_id, padre_id, codigo, nombre, url, icono, nivel, es_menu, activo |
| [Rol.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/app/Models/Rol.php) | `rols` | codigo, nombre, descripcion, nivel, activo |
| [TipoDocumento.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/app/Models/TipoDocumento.php) | `tipo_documentos` | codigo, nombre, abreviatura, formato_validacion, longitud_min, longitud_max, activo |
| [User.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/app/Models/User.php) | `users` | name, email, password, ... |

---

## 11. Solución de Problemas Comunes

| Problema | Causa / Solución |
|---|---|
| **"Livewire assets not loading"** | Asegúrate que la página tenga etiquetas `<head>` y `</body>`. El `inject_assets=true` lo requiere. Si no, agrega `@livewireStyles` en `<head>` y `@livewireScripts` antes de `</body>`. |
| **"Componente no se encuentra"** | Revisa namespace: clase PHP en `App\Livewire\...` o vista en `resources/views/livewire/...`. Recuerda: nombre de archivo `snake_case` (ej: `roles_crud.blade.php` se accede como `roles-crud`). |
| **Validación no funciona** | Asegúrate de llamar `$this->validate()` o usar `#[Rule]`. En Form Objects: `$this->form->validate()`. |
| **Paginación no cambia** | Usa el trait `use WithPagination;` y resetea la página con `$this->resetPage()` al cambiar filtros (hook `updatedBuscar()`). |
| **Eventos no llegan** | En Livewire 3 usa `$this->dispatch('evento', ...)` y escucha con `#[On('evento')]`. Para JavaScript: `window.Livewire.dispatch('evento')`. |
| **Modal no abre/cierra** | Para el componente [modal.blade.php](file:///c:/xampp7.4/htdocs/MDESistemaPIDE/SCPIDE/resources/views/components/modal.blade.php) del proyecto: usa `wire:model.live="propiedadBooleana"`. |
| **N+1 queries lentos** | Usa `->withCount(...)`, `->with('relacion')` y `->select(...)` para optimizar consulta. |

---

## 12. Recursos

- Documentación oficial Livewire 3: https://livewire.laravel.com/docs
- Documentación Volt: https://livewire.laravel.com/docs/volt
- Comandos útiles:
  - `php artisan livewire:make Nombre` → Clase PHP + Vista
  - `php artisan livewire:form NombreForm` → Form Object
  - `php artisan route:list` → Ver rutas (incluye Livewire full-page)
  - `php artisan route:clear` / `php artisan config:clear` / `php artisan view:clear` → Limpiar cachés al tener problemas
