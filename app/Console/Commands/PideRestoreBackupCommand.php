<?php

namespace App\Console\Commands;

use Database\Seeders\PideProductionDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PideRestoreBackupCommand extends Command
{
    protected $signature = 'pide:restore-backup
        {--dir= : Directorio con los JSON del backup (default: database/seeders/data/private)}
        {--force : Omitir la confirmación destructiva}';

    protected $description = 'Restaura el backup privado (JSON) en la base de datos local sin exponer los datos en el repositorio.';

    private const TABLES = [
        'historial_auditoria',
        'usuario_rol',
        'rol_modulo',
        'sesion_usuario',
        'usuarios',
        'personas',
        'modulos',
        'roles',
        'sistemas',
        'tipo_documento',
        'cat_estado',
    ];

    private const REQUIRED_FILES = [
        'cat_estado.json',
        'tipo_documento.json',
        'sistema.json',
        'persona.json',
        'rol.json',
        'usuario.json',
        'modulo.json',
        'rol_modulo.json',
        'usuario_rol.json',
        'historial_auditoria.json',
    ];

    public function handle(): int
    {
        $dir = rtrim((string) $this->option('dir'), '/\\');
        $dir = $dir !== '' ? $dir : database_path('seeders/data/private');

        if (! is_dir($dir)) {
            $this->error("No existe el directorio de backup: $dir");

            return self::FAILURE;
        }

        $missing = array_diff(self::REQUIRED_FILES, array_map('basename', glob("$dir/*.json") ?: []));
        if ($missing !== []) {
            $this->error('Faltan archivos en el backup: '.implode(', ', $missing));

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $tables = implode(', ', self::TABLES);
            if (! $this->confirm("Se vaciarán las tablas ($tables) y se insertarán los datos del backup. ¿Continuar?")) {
                $this->info('Cancelado.');

                return self::SUCCESS;
            }
        }

        $this->info('Vaciando tablas de datos...');

        Schema::disableForeignKeyConstraints();
        try {
            foreach (self::TABLES as $table) {
                DB::table($table)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info("Insertando datos desde $dir...");

        $seeder = new PideProductionDataSeeder;
        $seeder->dataDir = $dir;
        $seeder->run();

        foreach (self::TABLES as $table) {
            $this->line("  {$table}: ".DB::table($table)->count());
        }

        $this->info('Backup restaurado correctamente.');

        return self::SUCCESS;
    }
}
