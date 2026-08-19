<?php

namespace Tests\Feature;

use Database\Seeders\PideProductionDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PideRestoreBackupCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $fixtureDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = base_path('tests/fixtures/backup');
    }

    public function test_restores_backup_with_original_ids(): void
    {
        $this->seed(PideProductionDataSeeder::class);

        $this->assertSame(1, DB::table('personas')->count());
        $this->assertSame(1, DB::table('usuarios')->count());

        $this->artisan('pide:restore-backup', ['--dir' => $this->fixtureDir, '--force' => true])
            ->assertSuccessful();

        $this->assertSame(2, DB::table('personas')->count());
        $this->assertSame(2, DB::table('usuarios')->count());
        $this->assertSame(2, DB::table('roles')->count());
        $this->assertSame(2, DB::table('modulos')->count());
        $this->assertSame(2, DB::table('rol_modulo')->count());
        $this->assertSame(2, DB::table('usuario_rol')->count());
        $this->assertSame(1, DB::table('historial_auditoria')->count());

        $this->assertSame('PRACTICANTE', DB::table('roles')->where('id', 2)->value('nombre'));
        $this->assertSame('admin', DB::table('usuarios')->where('id', 1)->value('username'));
        $this->assertSame(1, DB::table('usuario_rol')->where('usuario_id', 1)->where('rol_id', 1)->count());
        $this->assertSame(1, DB::table('historial_auditoria')->where('tabla', 'ROL')->where('operacion', 'INSERT')->count());
    }

    public function test_restore_does_not_touch_iconos(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $iconosAntes = DB::table('iconos')->count();
        $this->assertGreaterThan(0, $iconosAntes);

        $this->artisan('pide:restore-backup', ['--dir' => $this->fixtureDir, '--force' => true])
            ->assertSuccessful();

        $this->assertSame($iconosAntes, DB::table('iconos')->count());
    }

    public function test_fails_when_directory_does_not_exist(): void
    {
        $this->artisan('pide:restore-backup', ['--dir' => base_path('tests/fixtures/no-existe'), '--force' => true])
            ->assertFailed();
    }

    public function test_fails_when_backup_is_incomplete(): void
    {
        $this->artisan('pide:restore-backup', ['--dir' => base_path('tests/fixtures/backup'), '--force' => true]);
        $dir = sys_get_temp_dir().'/pide-backup-incompleto';
        @mkdir($dir, 0777, true);
        @copy($this->fixtureDir.'/rol.json', $dir.'/rol.json');

        try {
            $this->artisan('pide:restore-backup', ['--dir' => $dir, '--force' => true])
                ->assertFailed();
        } finally {
            @unlink($dir.'/rol.json');
            @rmdir($dir);
        }
    }
}
