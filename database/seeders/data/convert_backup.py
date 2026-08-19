#!/usr/bin/env python3
"""Convierte INSERT de SQL Server legado a JSON compatibles con seeders Laravel.

La exportación completa siempre se escribe en un directorio privado. Con
``--public-dir`` también genera un conjunto mínimo, anonimizado y funcional
para crear únicamente al administrador y sus permisos.
"""

from __future__ import annotations

import argparse
import json
import re
import sys
from copy import deepcopy
from datetime import date, timedelta
from pathlib import Path
from typing import Any

import sqlparse


TABLE_FILES = {
    "CAT_ESTADO": "cat_estado.json",
    "TIPO_DOCUMENTO": "tipo_documento.json",
    "SISTEMA": "sistema.json",
    "PERSONA": "persona.json",
    "ROL": "rol.json",
    "USUARIO": "usuario.json",
    "MODULO": "modulo.json",
    "ROL_MODULO": "rol_modulo.json",
    "USUARIO_ROL": "usuario_rol.json",
    "HISTORIAL_AUDITORIA": "historial_auditoria.json",
}

INSERT_PATTERN = re.compile(
    r"^\s*INSERT\s+\[dbo\]\.\[(?P<table>[^\]]+)\]\s*"
    r"\((?P<columns>.*?)\)\s+VALUES\s*\((?P<values>.*)\)\s*;?\s*$",
    re.IGNORECASE,
)
CAST_PATTERN = re.compile(
    r"^CAST\(\s*(?P<value>.+?)\s+AS\s+(?P<type>[A-Za-z0-9]+)(?:\([0-9, ]+\))?\s*\)$",
    re.IGNORECASE,
)


def read_sql(path: Path) -> str:
    for encoding in ("utf-8-sig", "utf-16", "cp1252"):
        try:
            return path.read_text(encoding=encoding)
        except UnicodeError:
            continue
    raise ValueError(f"No se pudo detectar codificación de {path}")


def split_sql_list(raw: str) -> list[str]:
    values: list[str] = []
    start = 0
    depth = 0
    quoted = False
    index = 0

    while index < len(raw):
        char = raw[index]
        if char == "'":
            if quoted and index + 1 < len(raw) and raw[index + 1] == "'":
                index += 2
                continue
            quoted = not quoted
        elif not quoted:
            if char == "(":
                depth += 1
            elif char == ")":
                depth -= 1
            elif char == "," and depth == 0:
                values.append(raw[start:index].strip())
                start = index + 1
        index += 1

    values.append(raw[start:].strip())
    return values


def decode_datetime2(hex_value: str) -> str:
    payload = bytes.fromhex(hex_value.removeprefix("0x"))
    if len(payload) < 7:
        raise ValueError(f"DateTime2 binario inválido: {hex_value}")

    precision = payload[0]
    time_size = 3 if precision <= 2 else 4 if precision <= 4 else 5
    time_units = int.from_bytes(payload[1 : 1 + time_size], "little")
    day_count = int.from_bytes(payload[1 + time_size : 4 + time_size], "little")
    current_date = date(1, 1, 1) + timedelta(days=day_count)
    units_per_second = 10**precision
    seconds, fraction = divmod(time_units, units_per_second)
    hours, seconds = divmod(seconds, 3600)
    minutes, seconds = divmod(seconds, 60)
    fraction_text = f".{fraction:0{precision}d}" if precision else ""
    return f"{current_date.isoformat()}T{hours:02d}:{minutes:02d}:{seconds:02d}{fraction_text}"


def decode_value(raw: str) -> Any:
    value = raw.strip()
    if value.upper() == "NULL":
        return None

    cast = CAST_PATTERN.match(value)
    if cast:
        inner = cast.group("value").strip()
        sql_type = cast.group("type").upper()
        if sql_type == "DATETIME2" and inner.lower().startswith("0x"):
            return decode_datetime2(inner)
        return decode_value(inner)

    string_match = re.match(r"^N?'(.*)'$", value, re.DOTALL | re.IGNORECASE)
    if string_match:
        return string_match.group(1).replace("''", "'")

    if re.fullmatch(r"[-+]?\d+", value):
        return int(value)
    if re.fullmatch(r"[-+]?(?:\d+\.\d*|\d*\.\d+)", value):
        return float(value)
    if value.lower().startswith("0x"):
        return value

    raise ValueError(f"Valor SQL no soportado: {value[:100]}")


def parse_backup(path: Path) -> tuple[dict[str, list[dict[str, Any]]], dict[str, int]]:
    tables = {table: [] for table in TABLE_FILES}
    ignored: dict[str, int] = {}

    for line_number, line in enumerate(read_sql(path).splitlines(), start=1):
        if not line.lstrip().upper().startswith("INSERT "):
            continue
        parsed = sqlparse.parse(line)
        if not parsed or parsed[0].get_type() != "INSERT":
            raise ValueError(f"INSERT inválido en línea {line_number}")

        match = INSERT_PATTERN.match(line)
        if not match:
            raise ValueError(f"Formato INSERT no soportado en línea {line_number}")

        table = match.group("table").upper()
        if table not in tables:
            ignored[table] = ignored.get(table, 0) + 1
            continue

        columns = re.findall(r"\[([^\]]+)\]", match.group("columns"))
        raw_values = split_sql_list(match.group("values"))
        if len(columns) != len(raw_values):
            raise ValueError(
                f"Columnas ({len(columns)}) y valores ({len(raw_values)}) no coinciden "
                f"en línea {line_number}"
            )
        tables[table].append(dict(zip(columns, map(decode_value, raw_values))))

    return tables, ignored


def value_equals(left: Any, right: Any) -> bool:
    return str(left) == str(right)


def by_id(rows: list[dict[str, Any]], key: str, wanted: set[Any]) -> list[dict[str, Any]]:
    return [row for row in rows if any(value_equals(row.get(key), item) for item in wanted)]


def create_public_admin(
    tables: dict[str, list[dict[str, Any]]], admin_username: str
) -> dict[str, list[dict[str, Any]]]:
    roles = tables["ROL"]
    admin_role = next(
        (row for row in roles if str(row.get("ROL_codigo", "")).upper() == "ADMIN"),
        None,
    )
    if admin_role is None:
        raise ValueError("Backup no contiene rol con código ADMIN")

    role_id = admin_role["ROL_id"]
    assignments = [
        row
        for row in tables["USUARIO_ROL"]
        if value_equals(row.get("USR_rol_id"), role_id) and bool(row.get("USR_activo", 1))
    ]
    allowed_user_ids = {row["USR_usuario_id"] for row in assignments}
    admin_user = next(
        (
            row
            for row in tables["USUARIO"]
            if str(row.get("USU_username", "")).upper() == admin_username.upper()
            and any(value_equals(row.get("USU_id"), item) for item in allowed_user_ids)
        ),
        None,
    )
    if admin_user is None:
        admin_user = next(
            (
                row
                for row in tables["USUARIO"]
                if any(value_equals(row.get("USU_id"), item) for item in allowed_user_ids)
            ),
            None,
        )
    if admin_user is None:
        raise ValueError("Backup no contiene usuario asignado al rol ADMIN")

    user_id = admin_user["USU_id"]
    person_id = admin_user["USU_persona_id"]
    person = next(
        row for row in tables["PERSONA"] if value_equals(row.get("PER_id"), person_id)
    )
    document_type_id = person["PER_documento_tipo_id"]
    state_ids = {person["PER_estado_id"], admin_user["USU_estado_id"]}
    user_assignment = next(
        row
        for row in assignments
        if value_equals(row.get("USR_usuario_id"), user_id)
    )

    role_modules = by_id(tables["ROL_MODULO"], "ROM_rol_id", {role_id})
    module_ids = {row["ROM_modulo_id"] for row in role_modules}
    modules = by_id(tables["MODULO"], "MOD_id", module_ids)

    while True:
        parent_ids = {
            row["MOD_padre_id"] for row in modules if row.get("MOD_padre_id") is not None
        }
        missing = parent_ids - {row["MOD_id"] for row in modules}
        if not missing:
            break
        modules.extend(by_id(tables["MODULO"], "MOD_id", missing))

    system_ids = {row["MOD_sistema_id"] for row in modules}
    system_ids.update(row["ROM_sistema_id"] for row in role_modules)

    public_person = deepcopy(person)
    public_person.update(
        {
            "PER_documento_numero": "00000000",
            "PER_apellido_paterno": "ADMINISTRADOR",
            "PER_apellido_materno": None,
            "PER_nombres": "SISTEMA",
            "PER_fecha_nacimiento": None,
            "PER_telefono_fijo": None,
            "PER_telefono_movil": None,
            "PER_direccion": None,
            "PER_via_nombre": None,
            "PER_via_numero": None,
            "PER_via_mz": None,
            "PER_via_lote": None,
            "PER_foto_url": None,
        }
    )
    public_user = deepcopy(admin_user)
    public_user.update(
        {
            "USU_username": "admin",
            "USU_email": "admin@example.test",
            "USU_telefono": None,
            "USU_requiere_cambio_password": 1,
            "USU_intentos_fallidos": 0,
            "USU_fecha_ultimo_acceso": None,
            "USU_cui": "0",
        }
    )

    return {
        "CAT_ESTADO": by_id(tables["CAT_ESTADO"], "EST_id", state_ids),
        "TIPO_DOCUMENTO": by_id(
            tables["TIPO_DOCUMENTO"], "TDO_id", {document_type_id}
        ),
        "SISTEMA": by_id(tables["SISTEMA"], "SIS_id", system_ids),
        "PERSONA": [public_person],
        "ROL": [deepcopy(admin_role)],
        "USUARIO": [public_user],
        "MODULO": modules,
        "ROL_MODULO": role_modules,
        "USUARIO_ROL": [deepcopy(user_assignment)],
        "HISTORIAL_AUDITORIA": [],
    }


def write_tables(tables: dict[str, list[dict[str, Any]]], output_dir: Path) -> None:
    output_dir.mkdir(parents=True, exist_ok=True)
    for table, filename in TABLE_FILES.items():
        path = output_dir / filename
        path.write_text(
            json.dumps(tables.get(table, []), ensure_ascii=False, indent=4) + "\n",
            encoding="utf-8",
        )


def parse_args() -> argparse.Namespace:
    data_dir = Path(__file__).resolve().parent
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--input", type=Path, default=data_dir / "backup.sql")
    parser.add_argument("--output", type=Path, default=data_dir / "private")
    parser.add_argument(
        "--public-dir",
        type=Path,
        help="Genera también JSON públicos mínimos y anonimizados para administrador.",
    )
    parser.add_argument("--admin-username", default="ADMIN")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    if not args.input.is_file():
        print(f"No existe backup: {args.input}", file=sys.stderr)
        return 1

    try:
        tables, ignored = parse_backup(args.input)
        write_tables(tables, args.output)
        print(f"Exportación privada: {args.output.resolve()}")
        for table, filename in TABLE_FILES.items():
            print(f"  {filename}: {len(tables[table])} filas")
        if ignored:
            summary = ", ".join(f"{name}={count}" for name, count in sorted(ignored.items()))
            print(f"Tablas antiguas sin ORM omitidas: {summary}")

        if args.public_dir:
            public_tables = create_public_admin(tables, args.admin_username)
            write_tables(public_tables, args.public_dir)
            print(f"Exportación pública mínima: {args.public_dir.resolve()}")
        return 0
    except (ValueError, OSError) as error:
        print(f"Error: {error}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
