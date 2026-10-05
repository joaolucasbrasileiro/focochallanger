#!/bin/sh
set -eu

schedule="${IMPORT_CRON_SCHEDULE:-*/5 * * * *}"

if ! printf '%s\n' "$schedule" | grep -Eq '^[0-9*/,-]+ [0-9*/,-]+ [0-9*/,-]+ [0-9*/,-]+ [0-9*/,-]+$'; then
    echo "A expressao IMPORT_CRON_SCHEDULE e invalida: ${schedule}" >&2
    exit 1
fi

cat > /etc/cron.d/foco-imports <<EOF
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

${schedule} www-data /usr/bin/flock -n /tmp/foco-import.lock /bin/sh -c 'cd /var/www/html && /usr/local/bin/php artisan imports:run >> /var/www/html/storage/logs/import-cron.log 2>&1'
EOF

chmod 0644 /etc/cron.d/foco-imports

exec "$@"
