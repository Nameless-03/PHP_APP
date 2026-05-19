# run.ps1
# Script para inicializar y compilar todo el proyecto (Laravel + Vue) dentro de Docker con PostgreSQL

Write-Host "======================================================" -ForegroundColor Cyan
Write-Host " Iniciando el entorno de la aplicacion en Docker..." -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan

# 1. Comprobar e instalar dependencias de PHP iniciales si no existe la carpeta vendor
# Esto asegura que tengamos el framework antes de levantar el contenedor principal si clonamos de 0.
if (-not (Test-Path "vendor")) {
    Write-Host "Instalando dependencias de PHP iniciales (Composer)..." -ForegroundColor Yellow
    docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html laravelsail/php82-composer:latest composer install --ignore-platform-reqs
}

# 2. Levantar y actualizar los contenedores de Docker (crea y enciende)
Write-Host "Levantando los contenedores de Docker en segundo plano..." -ForegroundColor Yellow
docker compose up -d --build

# 3. Esperar unos segundos para que PostgreSQL esté completamente listo
Write-Host "Esperando a que la base de datos PostgreSQL se inicialice..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# 4. Asegurar dependencias de Laravel actualizadas (por si se actualizó composer.json)
Write-Host "Verificando dependencias de PHP..." -ForegroundColor Yellow
docker compose exec -T laravel.test composer install

# 5. Ejecutar migraciones (y opcionalmente seeders)
Write-Host "Ejecutando migraciones de la base de datos PostgreSQL..." -ForegroundColor Yellow
docker compose exec -T laravel.test php artisan migrate --force

# 6. Trabajar sobre el Frontend (Vue) si la carpeta existe
if (Test-Path "vue-frontend") {
    Write-Host "Instalando dependencias de Node.js para Vue..." -ForegroundColor Yellow
    docker compose exec -T laravel.test sh -c "cd vue-frontend && npm install"
    
    Write-Host "Compilando los archivos estaticos para produccion (build)..." -ForegroundColor Yellow
    docker compose exec -T laravel.test sh -c "cd vue-frontend && npm run build"
    
    Write-Host "Lanzando el servidor de desarrollo de Vite (Hot-Reload) en background..." -ForegroundColor Yellow
    # Se usa --host para exponerlo fuera del contenedor al localhost de Windows
    docker compose exec -d laravel.test sh -c "cd vue-frontend && npm run dev -- --host 0.0.0.0"
}

Write-Host "======================================================" -ForegroundColor Green
Write-Host "  ¡Todo ha sido compilado y levantado exitosamente!  " -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host " Backend (API Laravel) disponible en : http://localhost" -ForegroundColor Green
Write-Host " Frontend (Vite) disponible en       : http://localhost:5173" -ForegroundColor Green
Write-Host " DB PostgreSQL                       : puerto 5432" -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
