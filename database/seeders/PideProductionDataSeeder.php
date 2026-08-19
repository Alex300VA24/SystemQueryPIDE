<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PideProductionDataSeeder extends Seeder
{
    public ?string $dataDir = null;

    public function run(): void
    {
        $dir = $this->dataDir ?? database_path('seeders/data');

        $catEstado = $this->load("$dir/cat_estado.json");
        $tipoDocumento = $this->load("$dir/tipo_documento.json");
        $sistemas = $this->load("$dir/sistema.json");
        $iconos = $this->loadOptional("$dir/icono.json");
        $personas = $this->load("$dir/persona.json");
        $roles = $this->load("$dir/rol.json");
        $usuarios = $this->load("$dir/usuario.json");
        $modulos = $this->load("$dir/modulo.json");
        $rolModulo = $this->load("$dir/rol_modulo.json");
        $usuarioRol = $this->load("$dir/usuario_rol.json");
        $historial = $this->load("$dir/historial_auditoria.json");

        $this->insertWithId('cat_estado', array_map(fn ($r) => [
            'id' => $r['EST_id'],
            'codigo' => $r['EST_codigo'],
            'descripcion' => $r['EST_descripcion'],
            'aplicable_a' => $r['EST_aplicable_a'],
            'created_at' => $this->dt($r['EST_fecha_registro']),
            'updated_at' => $this->dt($r['EST_fecha_registro']),
        ], $catEstado));

        $this->insertWithId('tipo_documento', array_map(fn ($r) => [
            'id' => $r['TDO_id'],
            'codigo' => $r['TDO_codigo'],
            'nombre' => $r['TDO_nombre'],
            'abreviatura' => $r['TDO_abreviatura'] ?: null,
            'formato_validacion' => $r['TDO_formato_validacion'] ?: null,
            'longitud_min' => $r['TDO_longitud_min'] ?: null,
            'longitud_max' => $r['TDO_longitud_max'] ?: null,
            'activo' => (bool) $r['TDO_activo'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $tipoDocumento));

        $this->insertWithId('sistemas', array_map(fn ($r) => [
            'id' => $r['SIS_id'],
            'codigo' => $r['SIS_codigo'],
            'nombre' => $r['SIS_nombre'],
            'descripcion' => $r['SIS_descripcion'] ?: null,
            'url' => $r['SIS_url'] ?: null,
            'icono' => $r['SIS_icono'] ?: null,
            'version' => $r['SIS_version'] ?: null,
            'orden' => $r['SIS_orden'] ?: 0,
            'activo' => (bool) $r['SIS_activo'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $sistemas));

        $this->insertWithId('iconos', array_map(fn ($r) => [
            'id' => $r['ICO_id'],
            'clase' => $r['ICO_clase'],
            'nombre' => $r['ICO_nombre'],
            'grupo' => $r['ICO_grupo'] ?: null,
            'orden' => $r['ICO_orden'] ?: 0,
            'activo' => (bool) $r['ICO_activo'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $iconos));

        $this->insertWithId('personas', array_map(fn ($r) => [
            'id' => $r['PER_id'],
            'tipo_persona' => $r['PER_tipo_persona'] ?: 1,
            'documento_tipo_id' => $r['PER_documento_tipo_id'],
            'documento_numero' => $r['PER_documento_numero'],
            'apellido_paterno' => $r['PER_apellido_paterno'],
            'apellido_materno' => $r['PER_apellido_materno'] ?: null,
            'nombres' => $r['PER_nombres'],
            'fecha_nacimiento' => $r['PER_fecha_nacimiento'] ?: null,
            'sexo' => $r['PER_sexo'] ?: null,
            'telefono_fijo' => $r['PER_telefono_fijo'] ?: null,
            'telefono_movil' => $r['PER_telefono_movil'] ?: null,
            'direccion' => $r['PER_direccion'] ?: null,
            'via_nombre' => $r['PER_via_nombre'] ?: null,
            'via_numero' => $r['PER_via_numero'] ?: null,
            'via_mz' => $r['PER_via_mz'] ?: null,
            'via_lote' => $r['PER_via_lote'] ?: null,
            'foto_url' => $r['PER_foto_url'] ?: null,
            'estado_id' => $r['PER_estado_id'],
            'created_at' => $this->dt($r['PER_fecha_registro']),
            'updated_at' => $this->dt($r['PER_fecha_actualizacion']),
        ], $personas));

        $this->insertWithId('roles', array_map(fn ($r) => [
            'id' => $r['ROL_id'],
            'codigo' => $r['ROL_codigo'],
            'nombre' => $r['ROL_nombre'],
            'descripcion' => $r['ROL_descripcion'] ?: null,
            'nivel' => $r['ROL_nivel'] ?: 1,
            'activo' => (bool) $r['ROL_activo'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $roles));

        $this->insertWithId('usuarios', array_map(fn ($r) => [
            'id' => $r['USU_id'],
            'persona_id' => $r['USU_persona_id'],
            'username' => $r['USU_username'],
            'password_hash' => $r['USU_password_hash'],
            'email' => $r['USU_email'],
            'requiere_cambio_password' => (bool) $r['USU_requiere_cambio_password'],
            'intentos_fallidos' => $r['USU_intentos_fallidos'] ?: 0,
            'fecha_ultimo_acceso' => $this->dt($r['USU_fecha_ultimo_acceso']),
            'estado_id' => $r['USU_estado_id'],
            'fecha_actualizacion_password' => $this->dt($r['USU_fecha_actualizacion_password']),
            'telefono' => $r['USU_telefono'] ?? null,
            'cui' => $r['USU_cui'] ?: '0',
            'created_at' => $this->dt($r['USU_fecha_registro']),
            'updated_at' => $this->dt($r['USU_fecha_registro']),
        ], $usuarios));

        $this->insertWithId('modulos', array_map(fn ($r) => [
            'id' => $r['MOD_id'],
            'sistema_id' => $r['MOD_sistema_id'],
            'padre_id' => $r['MOD_padre_id'] ?: null,
            'codigo' => $r['MOD_codigo'],
            'nombre' => $r['MOD_nombre'],
            'descripcion' => $r['MOD_descripcion'] ?: null,
            'url' => $r['MOD_url'] ?: null,
            'icono' => $r['MOD_icono'] ?: null,
            'orden' => $r['MOD_orden'] ?: 0,
            'nivel' => $r['MOD_nivel'] ?: 1,
            'es_menu' => (bool) $r['MOD_es_menu'],
            'activo' => (bool) $r['MOD_activo'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $modulos));

        $this->insertWithId('rol_modulo', array_map(fn ($r) => [
            'id' => $r['ROM_id'],
            'rol_id' => $r['ROM_rol_id'],
            'sistema_id' => $r['ROM_sistema_id'],
            'modulo_id' => $r['ROM_modulo_id'],
            'fecha_asignacion' => $this->dt($r['ROM_fecha_asignacion']),
        ], $rolModulo));

        $this->insertWithId('usuario_rol', array_map(fn ($r) => [
            'id' => $r['USR_id'] + 1,
            'usuario_id' => $r['USR_usuario_id'],
            'rol_id' => $r['USR_rol_id'],
            'fecha_asignacion' => $this->dt($r['USR_fecha_asignacion']),
            'fecha_expiracion' => $r['USR_fecha_expiracion'] ?: null,
            'asignado_por' => $r['USR_asignado_por'] ?: null,
            'activo' => (bool) $r['USR_activo'],
        ], $usuarioRol));

        $this->insertWithId('historial_auditoria', array_map(fn ($r) => [
            'id' => $r['HIS_id'] + 1,
            'tabla' => $r['HIS_tabla'],
            'registro_id' => $r['HIS_registro_id'],
            'operacion' => $r['HIS_operacion'],
            'usuario_id' => $r['HIS_usuario_id'] ?: null,
            'fecha' => $this->dt($r['HIS_fecha']),
            'ip' => $r['HIS_ip'] ?: null,
            'datos_anteriores' => $r['HIS_datos_anteriores'] ?: null,
            'datos_nuevos' => $r['HIS_datos_nuevos'] ?: null,
            'observacion' => $r['HIS_observacion'] ?: null,
        ], $historial));
    }

    private function load(string $path): array
    {
        return json_decode(file_get_contents($path), true);
    }

    private function loadOptional(string $path): array
    {
        return is_file($path) ? $this->load($path) : [];
    }

    private function insertWithId(string $table, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $sqlsrv = DB::connection()->getDriverName() === 'sqlsrv';

        if ($sqlsrv) {
            DB::unprepared("SET IDENTITY_INSERT [$table] ON");
        }

        DB::table($table)->insert($rows);

        if ($sqlsrv) {
            DB::unprepared("SET IDENTITY_INSERT [$table] OFF");
        }
    }

    private function dt(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return str_replace('T', ' ', substr($value, 0, 19));
    }
}
