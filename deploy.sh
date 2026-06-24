#!/usr/bin/env bash
#
# Safe production deploy for loot4you.
#
# Why this exists: `docker compose up --build` rebuilds the heavy Dockerfile
# (npm ci + npm run build + composer + PHP ext compile) for EVERY service that
# declares `build:`. With app/queue/scheduler that was 3× at once → the 8 GB box
# ran out of RAM, swap-thrashed, pegged the CPU and went unreachable (CF 523).
#
# This script builds the image ONCE (the compose file now points queue/scheduler
# at the same `loot4you-app:latest` image) and deploys atomically.
#
# Usage:  ./deploy.sh
set -euo pipefail

cd "$(dirname "$0")"
COMPOSE="docker compose -f docker-compose.prod.yml"

echo "==> [1/6] Pulling latest code"
git pull --ff-only

echo "==> [2/6] Building app image (once)"
# Build only the `app` service; queue/scheduler reuse its image.
$COMPOSE build app

echo "==> [3/6] Recreating containers"
$COMPOSE up -d

echo "==> [4/6] Running migrations"
$COMPOSE exec -T app php artisan migrate --force

echo "==> [5/6] Caching config / routes / views"
$COMPOSE exec -T app php artisan optimize

echo "==> [6/6] Warming up (prime opcache + PHP-FPM so the first real request isn't cold)"
for _ in 1 2 3 4 5; do curl -sk -o /dev/null -H "Host: loot4you.gg" https://localhost/ || true; done

echo "==> Done. Current state:"
$COMPOSE ps
