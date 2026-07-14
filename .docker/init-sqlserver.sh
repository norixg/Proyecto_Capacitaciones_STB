#!/usr/bin/env bash
set -euo pipefail

SQLCMD=/opt/mssql-tools18/bin/sqlcmd
SERVER=sqlserver
DATABASE=db_capacitaciones_stb
SCRIPT=/docker-entrypoint-initdb.d/capacitaciones_stb.sql

echo "Comprobando si la base de datos de Capacitaciones STB necesita inicialización..."

INITIALIZED="$($SQLCMD \
    -S "$SERVER" \
    -U sa \
    -P "$MSSQL_SA_PASSWORD" \
    -C \
    -h -1 \
    -W \
    -Q "SET NOCOUNT ON; SELECT CASE WHEN DB_ID(N'$DATABASE') IS NOT NULL AND OBJECT_ID(N'[$DATABASE].[dbo].[users]', N'U') IS NOT NULL AND OBJECT_ID(N'[$DATABASE].[dbo].[capacitacion]', N'U') IS NOT NULL AND EXISTS (SELECT 1 FROM [$DATABASE].[dbo].[rol]) AND EXISTS (SELECT 1 FROM [$DATABASE].[dbo].[permissions]) THEN 1 ELSE 0 END;")"

if echo "$INITIALIZED" | grep -q '^1$'; then
    echo "La base de datos ya está inicializada; no se volverá a ejecutar el script destructivo."
    exit 0
fi

echo "Importando $SCRIPT. Este proceso puede tardar unos minutos..."

$SQLCMD \
    -S "$SERVER" \
    -U sa \
    -P "$MSSQL_SA_PASSWORD" \
    -C \
    -b \
    -i "$SCRIPT"

$SQLCMD \
    -S "$SERVER" \
    -U sa \
    -P "$MSSQL_SA_PASSWORD" \
    -C \
    -d "$DATABASE" \
    -b \
    -Q "IF OBJECT_ID(N'dbo.users', N'U') IS NULL THROW 50001, 'La tabla dbo.users no fue creada.', 1; SELECT 'Base de datos inicializada correctamente.' AS resultado;"

echo "Inicialización de SQL Server completada."
