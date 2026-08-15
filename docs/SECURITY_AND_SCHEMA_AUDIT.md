# Auditoría inicial de seguridad y esquema

## Alcance

Revisión estática del PHP legado y esquema Laravel disponible. No se eliminó ninguna tabla o columna: conexión del esquema original no respondió durante auditoría y borrar sin telemetría, respaldo y ventana de reversión sería inseguro.

## Hallazgos corregidos en Laravel

- SQL dinámico legado queda fuera del flujo nuevo. Usuarios y roles usan Eloquent, listas blancas para ordenamiento y parámetros enlazados.
- Escrituras relacionadas usan `DB::transaction()`.
- Contraseñas usan `Hash`; nunca se devuelven a vistas.
- Login mantiene rate limiting de Breeze y ahora bloquea cuentas inactivas.
- Salida invalida sesión y regenera token CSRF.
- Blade escapa salida por defecto. No se usa HTML aportado por usuario.
- Livewire valida identificadores, correo, filtros y claves antes de ejecutar servicios.
- Servicios impiden autoeliminación y eliminación de roles asignados.
- PHPUnit usa SQLite en memoria; pruebas nunca apuntan a MySQL de desarrollo/producción.

## Esquema normalizado inicial

- `users`: agrega `area`, `activo` e índices de operación.
- `rols`: catálogo conservado por compatibilidad con migración existente.
- `role_user`: relación muchos-a-muchos con claves foráneas y clave primaria compuesta.
- `personas`: documento, nombres, contacto, estado e índices útiles.
- `modulos`: clave foránea autorreferente corregida, código único por sistema e índice de menú.
- Índices duplicados sobre columnas `unique` fueron retirados de migraciones nuevas.

## Candidatos legacy, no autorizados para borrar

`Practicante` y `Asistencia` aparecen únicamente en `DashboardRepository.php`, pero pueden pertenecer a otro dominio. Tablas/columnas realmente obsoletas requieren: inventario de consultas en producción, conteos, dependencias, respaldo probado y migración reversible.

## Siguiente corte seguro

1. Exportar metadatos de BD original: tablas, columnas, claves, índices, vistas y procedimientos.
2. Cruzar cada objeto con repositorios, procedimientos y logs de consultas de 30–90 días.
3. Marcar `mantener`, `migrar`, `archivar` o `eliminar` con dueño funcional.
4. Crear migraciones de archivo primero; eliminar solo en despliegue posterior.
