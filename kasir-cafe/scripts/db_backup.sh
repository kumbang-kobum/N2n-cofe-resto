#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$ROOT_DIR/.env"

if [ ! -f "$ENV_FILE" ]; then
  echo ".env not found"
  exit 1
fi

DB_CONNECTION="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_CONNECTION"] ?? "";' "$ENV_FILE")"
DB_HOST="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_HOST"] ?? "127.0.0.1";' "$ENV_FILE")"
DB_PORT="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_PORT"] ?? "3306";' "$ENV_FILE")"
DB_DATABASE="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_DATABASE"] ?? "";' "$ENV_FILE")"
DB_USERNAME="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_USERNAME"] ?? "";' "$ENV_FILE")"
DB_PASSWORD="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_PASSWORD"] ?? "";' "$ENV_FILE")"

BACKUP_DIR="$ROOT_DIR/backups"
mkdir -p "$BACKUP_DIR"

TS="$(date +"%d%m%Y%H%M")"

if [ "$DB_CONNECTION" = "sqlite" ]; then
  if [ -z "$DB_DATABASE" ]; then
    echo "DB_DATABASE empty for sqlite"
    exit 1
  fi
  cp "$DB_DATABASE" "$BACKUP_DIR/${DB_DATABASE}_${TS}.sqlite"
  echo "Backup created: $BACKUP_DIR/${DB_DATABASE}_${TS}.sqlite"
  ls -1t "$BACKUP_DIR"/*sqlite 2>/dev/null | tail -n +21 | xargs -r rm -f
  exit 0
fi

if [ "$DB_CONNECTION" != "mysql" ]; then
  echo "Unsupported DB_CONNECTION: $DB_CONNECTION"
  exit 1
fi

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
  echo "DB_DATABASE/DB_USERNAME empty"
  exit 1
fi

MYSQL_PWD="$DB_PASSWORD" mysqldump \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USERNAME" \
  "$DB_DATABASE" > "$BACKUP_DIR/${DB_DATABASE}_${TS}.sql"

echo "Backup created: $BACKUP_DIR/${DB_DATABASE}_${TS}.sql"
ls -1t "$BACKUP_DIR"/*sql 2>/dev/null | tail -n +21 | xargs -r rm -f
