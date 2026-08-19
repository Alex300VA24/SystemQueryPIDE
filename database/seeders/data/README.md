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
