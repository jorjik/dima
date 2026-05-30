#!/bin/bash
set -e

# ──────────────────────────────────────────────
# deploy.sh — Automated deployment via SSH
# ──────────────────────────────────────────────
# Usage:
#   ./deploy.sh                  # deploy with auto-commit (uses last commit)
#   ./deploy.sh "my message"     # deploy with a new commit message
#   ./deploy.sh --no-push        # deploy without pushing (already pushed)
#   ./deploy.sh --help           # show this help
# ──────────────────────────────────────────────

LARAVEL_DIR="app/laravel"

# ─── colors ───
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

info()  { echo -e "${CYAN}[INFO]${NC} $1"; }
ok()    { echo -e "${GREEN}[OK]${NC}   $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
fail()  { echo -e "${RED}[FAIL]${NC} $1"; exit 1; }

# ─── help ───
if [[ "$1" == "--help" ]]; then
    sed -n '/^# Usage:/,/^# ─/p' "$0" | sed 's/^# //; s/^#$//'
    exit 0
fi

# ─── check prerequisites ───
command -v sshpass >/dev/null 2>&1 || fail "sshpass is not installed. Install it: apt install sshpass"
command -v git >/dev/null 2>&1 || fail "git is not installed"

# ─── read SSH credentials from .env ───
ENV_FILE="$LARAVEL_DIR/.env"
if [ ! -f "$ENV_FILE" ]; then
    fail ".env file not found at $ENV_FILE"
fi

DEPLOY_HOST=$(grep '^DEPLOY_HOST=' "$ENV_FILE" | cut -d= -f2)
DEPLOY_USER=$(grep '^DEPLOY_USER=' "$ENV_FILE" | cut -d= -f2)
DEPLOY_PASSWORD=$(grep '^DEPLOY_PASSWORD=' "$ENV_FILE" | cut -d= -f2)

if [ -z "$DEPLOY_HOST" ] || [ -z "$DEPLOY_USER" ] || [ -z "$DEPLOY_PASSWORD" ]; then
    fail "Missing DEPLOY_HOST, DEPLOY_USER or DEPLOY_PASSWORD in $ENV_FILE"
fi

info "Server: $DEPLOY_USER@$DEPLOY_HOST"

# ─── commit & push ───
if [[ "$1" != "--no-push" ]]; then
    if [ -n "$1" ] && [[ "$1" != --* ]]; then
        COMMIT_MSG="$1"
    else
        COMMIT_MSG="deploy: $(date '+%Y-%m-%d %H:%M')"
    fi

    if [ -n "$(git status --porcelain)" ]; then
        info "Committing changes..."
        git add -A
        git commit -m "$COMMIT_MSG" || warn "Nothing to commit"
    else
        info "Working tree is clean, skipping commit."
    fi

    info "Pushing to origin/main..."
    git push origin main || fail "Git push failed"
    ok "Pushed to origin/main"
else
    info "Skipping push (--no-push)"
fi

# ─── SSH deploy ───
info "Connecting to server..."

if sshpass -p "$DEPLOY_PASSWORD" ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 "$DEPLOY_USER@$DEPLOY_HOST" bash <<'REMOTESCRIPT'
set -e

cd /var/www/html

# Remove known untracked files that break git pull
rm -f app/laravel/database/migrations/2026_05_30_071839_add_opacity_fields_to_site_settings_table.php
rm -f app/laravel/config/filament.php

# Stash local changes if any
STASHED=0
if [ -n "$(git status --porcelain)" ]; then
    echo '[SERVER] Stashing local changes...'
    git stash
    STASHED=1
fi

echo '[SERVER] Pulling latest code...'
git pull origin main

# Restore stash if any
if [ "$STASHED" = "1" ]; then
    echo '[SERVER] Restoring stashed changes...'
    git stash pop || true
fi

echo '[SERVER] Running Laravel commands...'
cd /var/www/html/app/laravel
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --force 2>/dev/null || true

echo '[SERVER] Done'
REMOTESCRIPT
then
    ok "Deployment completed successfully!"
    echo ""
    echo "  Site: https://dima.by"
else
    fail "Deployment failed. Check the output above for details."
fi
