<?php

namespace App\Support;

use App\Models\Modulo;
use App\Models\Usuario;
use Illuminate\Support\Collection;

final class DashboardNavigation
{
    private const CODE_KEYS = [
        'INI' => 'inicio',
        'DNI' => 'dni',
        'RUC' => 'ruc',
        'PAR' => 'partidas',
        'USU' => 'usuarios',
        'RUSU' => 'usuarios',
        'CROL' => 'roles',
        'MOD' => 'modulos',
        'CMOD' => 'modulos',
        'APAS' => 'password',
        'AYU' => 'ayuda',
    ];

    private const URL_KEYS = [
        'inicio' => 'inicio',
        'dni' => 'dni',
        'ruc' => 'ruc',
        'partidas' => 'partidas',
        'crear-usuario' => 'usuarios',
        'actualizar-pass' => 'password',
        'crear-roles' => 'roles',
        'crear-modulo' => 'modulos',
        'ayuda' => 'ayuda',
    ];

    public function forUser(Usuario $user): array
    {
        $roleIds = $user->roles()->wherePivot('activo', true)->where('roles.activo', true)->pluck('roles.id');

        if ($roleIds->isEmpty()) {
            return [];
        }

        $modules = Modulo::query()
            ->where('activo', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('roles.id', $roleIds))
            ->orderBy('orden')
            ->get(['id', 'padre_id', 'codigo', 'nombre', 'url', 'icono', 'orden', 'nivel']);

        return $this->tree($modules);
    }

    public function keysFor(Usuario $user): array
    {
        return collect($this->forUser($user))
            ->flatMap(fn (array $module) => [
                ...($module['key'] ? [$module['key']] : []),
                ...array_column($module['children'], 'key'),
            ])
            ->filter()
            ->values()
            ->all();
    }

    private function tree(Collection $modules): array
    {
        $children = $modules->groupBy('padre_id');

        return $modules
            ->whereNull('padre_id')
            ->sortBy('orden')
            ->map(fn (Modulo $module) => $this->mapModule($module, $children))
            ->values()
            ->all();
    }

    private function mapModule(Modulo $module, Collection $children): array
    {
        $tabs = $children->get($module->id, collect())
            ->sortBy('orden')
            ->filter(fn (Modulo $child) => (int) $child->nivel >= 3)
            ->map(fn (Modulo $child) => [
                'key' => self::URL_KEYS[trim(basename((string) $child->url), '/')] ?? self::CODE_KEYS[$child->codigo] ?? trim(basename((string) $child->url), '/'),
                'code' => $child->codigo,
                'label' => $child->nombre,
                'icon' => $child->icono ?: 'grid',
            ])
            ->values()
            ->all();

        return [
            'key' => self::URL_KEYS[trim(basename((string) $module->url), '/')] ?? self::CODE_KEYS[$module->codigo] ?? trim(basename((string) $module->url), '/'),
            'code' => $module->codigo,
            'label' => $module->nombre,
            'icon' => $module->icono ?: 'grid',
            'nivel' => (int) $module->nivel,
            'children' => $children->get($module->id, collect())
                ->sortBy('orden')
                ->filter(fn (Modulo $child) => (int) $child->nivel < 3)
                ->map(fn (Modulo $child) => $this->mapModule($child, $children))
                ->values()
                ->all(),
            'tabs' => $tabs,
        ];
    }
}
