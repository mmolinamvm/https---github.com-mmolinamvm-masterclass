#!/bin/bash
# ============================================
# DEPLOY MASTERCLASS -> cdmon (FTP/SFTP)
# ============================================
# Ús:
#   ./bin/deploy.sh              # Sube los archivos modificados (git diff HEAD~1)
#   ./bin/deploy.sh --all        # Sube todo el proyecto
#   ./bin/deploy.sh --dry-run    # Muestra qué se subiría sin subir nada
#
# Credenciales en config/ftp.txt (formato: host|usuario|password|directorio_remoto)
# ============================================

set -e

CONFIG_FILE="config/ftp.txt"

if [ ! -f "$CONFIG_FILE" ]; then
    echo "ERROR: No existe $CONFIG_FILE"
    echo "Crea el archivo con el formato: host|usuario|password|directorio_remoto"
    exit 1
fi

IFS='|' read -r FTP_HOST FTP_USER FTP_PASS FTP_DIR < "$CONFIG_FILE"

if [ -z "$FTP_HOST" ] || [ -z "$FTP_USER" ] || [ -z "$FTP_PASS" ] || [ -z "$FTP_DIR" ]; then
    echo "ERROR: config/ftp.txt debe tener el formato: host|usuario|password|directorio_remoto"
    exit 1
fi

# Archivos que NUNCA se suben
EXCLUDE=(
    ".git"
    ".gitignore"
    "config/token.txt"
    "config/ftp.txt"
    "sql/init_db_user.sql"
    "bin/deploy.sh"
)

is_excluded() {
    local f="$1"
    for e in "${EXCLUDE[@]}"; do
        if [[ "$f" == "$e"* ]]; then
            return 0
        fi
    done
    return 1
}

# Determinar archivos a subir
if [ "$1" == "--all" ]; then
    FILES=$(git ls-files)
elif [ "$1" == "--dry-run" ]; then
    FILES=$(git diff --name-only HEAD~1)
    echo "=== DRY RUN: archivos que se subirían ==="
    for f in $FILES; do
        if ! is_excluded "$f"; then
            echo "  $f"
        fi
    done
    exit 0
else
    FILES=$(git diff --name-only HEAD~1)
fi

if [ -z "$FILES" ]; then
    echo "No hay cambios que subir."
    exit 0
fi

echo "=== Subiendo a $FTP_HOST:$FTP_DIR ==="
COUNT=0

for f in $FILES; do
    if is_excluded "$f"; then
        echo "  [SKIP] $f"
        continue
    fi
    if [ ! -f "$f" ]; then
        echo "  [SKIP] $f (no existe)"
        continue
    fi

    # Crear directorio remoto si no existe
    REMOTE_DIR="$FTP_DIR/$(dirname "$f")"
    curl -s --ftp-create-dirs "ftp://$FTP_HOST/$REMOTE_DIR/" --user "$FTP_USER:$FTP_PASS" > /dev/null 2>&1 || true

    # Subir archivo
    if curl -s -T "$f" "ftp://$FTP_HOST/$REMOTE_DIR/$(basename "$f")" --user "$FTP_USER:$FTP_PASS"; then
        echo "  [OK] $f"
        COUNT=$((COUNT + 1))
    else
        echo "  [ERROR] $f"
    fi
done

echo "=== Despliegue completado: $COUNT archivos subidos ==="