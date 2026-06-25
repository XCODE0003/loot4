#!/usr/bin/env bash
# One-shot migration bring-up on the NEW server.
set -uo pipefail
cd /var/www/loot4you
COMPOSE="docker compose -f docker-compose.prod.yml"
ROOTP="$(grep '^DB_ROOT_PASSWORD=' .env | cut -d= -f2- | tr -d '\r')"

echo "=== START $(date) ==="
echo "=== build app image (once) ==="
$COMPOSE build app

echo "=== up -d ==="
$COMPOSE up -d

echo "=== wait for mysql ==="
for i in $(seq 1 50); do
  if docker exec loot4you-mysql-1 mysqladmin ping -uroot -p"$ROOTP" 2>/dev/null | grep -q "alive"; then
    echo "mysql alive after ${i} tries"; break
  fi
  sleep 3
done

echo "=== import DB dump ==="
if docker exec -i loot4you-mysql-1 mysql -uroot -p"$ROOTP" loot4 < /var/www/loot4you/loot4_dump.sql; then
  echo "IMPORT_OK"
else
  echo "IMPORT_FAILED"
fi

echo "=== row counts (data check) ==="
docker exec loot4you-mysql-1 mysql -uroot -p"$ROOTP" -N -e \
  "SELECT 'products',COUNT(*) FROM loot4.products UNION SELECT 'games',COUNT(*) FROM loot4.games UNION SELECT 'orders',COUNT(*) FROM loot4.orders UNION SELECT 'users',COUNT(*) FROM loot4.users UNION SELECT 'settings',COUNT(*) FROM loot4.settings" 2>/dev/null

echo "=== artisan optimize ==="
$COMPOSE exec -T app php artisan optimize 2>&1 | tail -4

echo "=== containers ==="
docker ps --format "{{.Names}}|{{.Status}}" | grep loot4

echo "=== site self-check ==="
curl -s -o /dev/null -w "http=%{http_code}\n" http://localhost/
curl -sk -o /dev/null -w "https=%{http_code}\n" https://localhost/ -H "Host: loot4you.gg"

echo "=== DONE $(date) ==="
