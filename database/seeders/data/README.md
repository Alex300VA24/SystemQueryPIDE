# Conversión de respaldo legado

El respaldo completo y las exportaciones privadas nunca deben versionarse.

```powershell
cd database\seeders\data
py -m venv env
.\env\Scripts\Activate.ps1
python -m pip install uv
$env:UV_CACHE_DIR = "$PWD\env\uv-cache"
python -m uv pip install -r requirements.txt
python convert_backup.py --public-dir .
```

La ejecución crea todos los JSON del ORM en `private/` y deja en este directorio
solo datos anonimizados mínimos para el administrador. El hash de contraseña se
conserva desde el administrador legado; debe cambiarse al iniciar sesión.

## Restaurar el backup completo en la base de datos local

Los JSON en `private/` contienen todos los datos del sistema legado (personas,
usuarios, roles, módulos, historial, etc.). Para cargarlos en la BD local sin
exponerlos en el repositorio:

```powershell
php artisan pide:restore-backup
```

El comando:

- Vacía las tablas de datos (`cat_estado`, `tipo_documento`, `sistemas`,
  `personas`, `roles`, `usuarios`, `modulos`, `rol_modulo`, `usuario_rol`,
  `historial_auditoria`, `sesion_usuario`) e inserta los registros con sus IDs
  originales. La tabla `iconos` no se toca.
- Pide confirmación antes de ejecutar (omitirla con `--force`).
- Acepta otro directorio con `--dir=<ruta>`.

> Importante: `database/seeders/data/private/`, `backup.sql` y `env/` están en
> `.gitignore`; nunca se suben al remoto.
