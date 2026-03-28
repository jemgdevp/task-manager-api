#!/bin/bash
# deploy.sh
set -e

cd /app

# Instalar dependencias de Composer si faltan
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist
else
    echo "Vendor exists, skipping composer install"
fi

# Instalar dependencias de Node y compilar assets si build no existe
if [ ! -d public/build ]; then
    npm install -g pnpm
    pnpm install --frozen-lockfile
    pnpm run build
else
    echo "Assets exist, skipping npm install and build"
fi

# Limpiar caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar storage:link para crear enlaces simbólicos a storage (local)(S3 Primoridla)
php artisan storage:link

# Validar y exportar documentación OpenAPI con Scramble
php artisan scramble:analyze
php artisan scramble:export --path=storage/api-docs/api-docs.json

# Ejecutar migraciones (manual)
#php artisan migrate --seed --force

echo "Deploy completed successfully"
