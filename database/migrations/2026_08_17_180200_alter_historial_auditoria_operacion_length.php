<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->sqlite()) {
            return;
        }

        DB::unprepared($this->sqlsrv()
            ? 'ALTER TABLE historial_auditoria ALTER COLUMN operacion NVARCHAR(50) NOT NULL'
            : 'ALTER TABLE historial_auditoria MODIFY operacion VARCHAR(50) NOT NULL');
    }

    public function down(): void
    {
        if ($this->sqlite()) {
            return;
        }

        DB::unprepared($this->sqlsrv()
            ? 'ALTER TABLE historial_auditoria ALTER COLUMN operacion NVARCHAR(10) NOT NULL'
            : 'ALTER TABLE historial_auditoria MODIFY operacion VARCHAR(10) NOT NULL');
    }

    private function sqlsrv(): bool
    {
        return DB::connection()->getDriverName() === 'sqlsrv';
    }

    private function sqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
};
