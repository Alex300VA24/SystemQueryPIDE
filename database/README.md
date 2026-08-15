# Esquema PIDE optimizado

`backupPide.sql` fue usado como fuente. Laravel conserva solo entidades requeridas por autenticación, navegación y administración actual:

- `users`: reemplaza `USUARIO`; Laravel maneja hash, sesión y recuperación.
- `rols` + `role_user`: reemplazan `ROL` y `USUARIO_ROL`.
- `modulos` + `module_role`: reemplazan `MODULO` y `ROL_MODULO`.
- `modulos.padre_id`: conserva jerarquía del sidebar.

No se recrean `SISTEMA`/`ROL_SISTEMA` porque SCPIDE representa un solo sistema. Tampoco `CAT_ESTADO`, `PERSONA`, `TIPO_DOCUMENTO`, `TIPO_PERSONAL`, empresas, geografía, constantes, auditoría legacy ni sesiones custom: ninguna participa en flujos Laravel actuales. Estados simples usan booleanos; sesiones usan driver Laravel.

Datos operativos del dump no se importan. `PideCatalogSeeder` carga catálogo mínimo vigente, omite módulos muertos `MANTENIMIENTO` y `PRUEBA`, y agrega `PAPELETAS`, pantalla existente en SCPIDE.
