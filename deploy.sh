#!/bin/bash

# Script de deployment para Laravel en producción
# Este script se ejecuta automáticamente al iniciar el contenedor

echo "🚀 Iniciando deployment de Laravel..."

# Verificar que estamos en el directorio correcto
cd /app || exit 1

# Optimizar configuración
echo "⚙️ Optimizando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones (solo las pendientes)
echo "📦 Ejecutando migraciones..."
php artisan migrate --force

# Crear/actualizar usuario initial
echo "👤 Configurando usuario initial..."
php artisan db:seed --class=InitialUserSeeder --force

# Crear link de storage (si no existe)
echo "🔗 Creando link de storage..."
php artisan storage:link || true

# Limpiar caché de aplicación
echo "🧹 Últimos Preparativos..."
php artisan optimize:clear

echo "✅ Deployment completado exitosamente!"
