<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reorganiza los módulos de consulta de SUNAT: los niveles 3 se muestran
     * como pestañas dentro del módulo padre (nivel 2).
     *
     * La migración es idempotente y solo actúa cuando ya existen los datos de
     * producción; en instalaciones frescas el seeder construye la estructura.
     */
    public function up(): void
    {
        if (! Schema::hasTable('modulos')) {
            return;
        }

        $sistemaId = 2;
        $sunat = DB::table('modulos')->where('codigo', 'RUC')->where('sistema_id', $sistemaId)->first();

        if ($sunat === null) {
            return;
        }

        DB::table('modulos')
            ->where('codigo', 'COB')
            ->where('sistema_id', $sistemaId)
            ->update([
                'padre_id' => $sunat->id,
                'nivel' => 3,
                'nombre' => 'Consulta Cobranza Coactiva',
                'url' => '/pide/consultas/sunat/ccoactiva',
                'orden' => 2,
                'es_menu' => 1,
                'activo' => 1,
            ]);

        $contribuyente = DB::table('modulos')
            ->where('codigo', 'CRUC')
            ->where('sistema_id', $sistemaId)
            ->exists();

        if (! $contribuyente) {
            $nuevoId = DB::table('modulos')->insertGetId([
                'sistema_id' => $sistemaId,
                'padre_id' => $sunat->id,
                'codigo' => 'CRUC',
                'nombre' => 'Consulta Contribuyente',
                'descripcion' => 'Consulta por RUC el servidor de SUNAT',
                'url' => '/pide/consultas/sunat/contribuyente',
                'icono' => 'fa-solid fa-building-columns',
                'orden' => 1,
                'nivel' => 3,
                'es_menu' => 1,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $roles = DB::table('rol_modulo')
                ->where('modulo_id', $sunat->id)
                ->distinct()
                ->pluck('rol_id');

            foreach ($roles as $rolId) {
                DB::table('rol_modulo')->insert([
                    'rol_id' => $rolId,
                    'sistema_id' => $sistemaId,
                    'modulo_id' => $nuevoId,
                    'fecha_asignacion' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('modulos')) {
            return;
        }

        $sistemaId = 2;
        $contribuyente = DB::table('modulos')->where('codigo', 'CRUC')->where('sistema_id', $sistemaId)->first();

        if ($contribuyente !== null) {
            DB::table('rol_modulo')->where('modulo_id', $contribuyente->id)->delete();
            DB::table('modulos')->where('id', $contribuyente->id)->delete();
        }

        DB::table('modulos')
            ->where('codigo', 'COB')
            ->where('sistema_id', $sistemaId)
            ->update([
                'padre_id' => 2,
                'nivel' => 2,
                'nombre' => 'COBRANZA COACTIVA',
                'url' => '/pide/consultas/ccoactiva',
                'orden' => 7,
            ]);
    }
};
