#!/usr/bin/env bash
set -euo pipefail

if [ $# -lt 1 ]; then
  echo "Usage: $0 <backup-file.sql|backup-file.sqlite>"
  exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
  echo "Backup file not found: $BACKUP_FILE"
  exit 1
fi

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$ROOT_DIR/.env"

DB_CONNECTION="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_CONNECTION"] ?? "";' "$ENV_FILE")"
DB_HOST="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_HOST"] ?? "127.0.0.1";' "$ENV_FILE")"
DB_PORT="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_PORT"] ?? "3306";' "$ENV_FILE")"
DB_DATABASE="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_DATABASE"] ?? "";' "$ENV_FILE")"
DB_USERNAME="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_USERNAME"] ?? "";' "$ENV_FILE")"
DB_PASSWORD="$(php -r '$e=parse_ini_file($argv[1]); echo $e["DB_PASSWORD"] ?? "";' "$ENV_FILE")"

if [ "$DB_CONNECTION" = "sqlite" ]; then
  if [ -z "$DB_DATABASE" ]; then
    echo "DB_DATABASE empty for sqlite"
    exit 1
  fi
  cp "$BACKUP_FILE" "$DB_DATABASE"
  echo "Restore complete: $DB_DATABASE"
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

MYSQL_PWD="$DB_PASSWORD" mysql \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USERNAME" \
  "$DB_DATABASE" < "$BACKUP_FILE"

echo "Restore complete."
