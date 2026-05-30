# Deployment Rule

When deploying Laravel applications, use SSH-based deployment.

## Prerequisites
- SSH_PRIVATE_KEY in GitHub Secrets
- DEPLOY_HOST and DEPLOY_USER secrets
- PHP/Composer/Node on server

## Method
GitHub Actions workflow deploys via rsync over SSH after building assets.
Manual: git pull, composer install --no-dev, npm run build, artisan migrate --force.
