#!/bin/sh
set -eu

schedule="${IMPORT_CRON_SCHEDULE:-*/5 * * * *}"
environment_file="/run/foco-import.env"

if ! printf '%s\n' "$schedule" | grep -Eq '^[0-9*/,-]+ [0-9*/,-]+ [0-9*/,-]+ [0-9*/,-]+ [0-9*/,-]+$'; then
    echo "A expressao IMPORT_CRON_SCHEDULE e invalida: ${schedule}" >&2
    exit 1
fi

umask 077

for key in \
    APP_ENV \
    APP_DEBUG \
    APP_URL \
    DB_CONNECTION \
    DB_HOST \
    DB_PORT \
    DB_DATABASE \
    DB_USERNAME \
    DB_PASSWORD \
    IMPORT_HOTELS_PATH \
    IMPORT_ROOMS_PATH \
    IMPORT_RESERVATIONS_PATH \
    IMPORT_ARCHIVE_PATH
do
    value="$(printenv "$key" || true)"

    if [ -n "$value" ]; then
        printf '%s=%s\n' "$key" "$value"
    fi
done > "$environment_file"

chmod 0600 "$environment_file"

cat > /etc/cron.d/foco-imports <<EOF
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

${schedule} root /usr/bin/flock -n /tmp/foco-import.lock /usr/local/bin/run-imports
EOF

chmod 0644 /etc/cron.d/foco-imports

exec "$@"
