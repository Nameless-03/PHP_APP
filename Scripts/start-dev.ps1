$projectRoot = (Get-Item -Path "$PSScriptRoot\..").FullName

Write-Host "=================================================" -ForegroundColor Cyan
Write-Host " Iniciando Entorno de Desarrollo Local           " -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan

# Mover a la raíz del proyecto
Set-Location $projectRoot

# --- 1. BACKEND (Laravel) ---
Write-Host "`n[1/4] Verificando Backend (Laravel/Docker)..." -ForegroundColor Yellow

$backendPath = Join-Path $projectRoot "laravel-temp"
if (Test-Path $backendPath) {
    Set-Location $backendPath

    # Verificar e instalar dependencias de PHP (Necesario para construir Sail la primera vez)
    if (Test-Path "composer.json") {
        if (Test-Path "vendor") {
            Write-Host "Las dependencias de PHP ya están instaladas. Saltando..." -ForegroundColor Green
        } else {
            Write-Host "Instalando dependencias de Composer..." -ForegroundColor Yellow
            $composerPhar = Join-Path $projectRoot "composer.phar"
            if (Test-Path $composerPhar) {
                php $composerPhar install
            } else {
                composer install
            }
        }
    }

    # Configuración del entorno
    if (-not (Test-Path ".env")) {
        if (Test-Path ".env.example") {
            Write-Host "Copiando .env.example a .env..." -ForegroundColor Yellow
            Copy-Item ".env.example" ".env"
        } else {
            Write-Host "Advertencia: No se encontró .env ni .env.example" -ForegroundColor Red
        }
    }

    if (Test-Path "docker-compose.yml") {
        Write-Host "[2/4] Iniciando contenedores Docker (Laravel Sail)..." -ForegroundColor Green
        # Iniciamos Docker, luego generamos la key (solo funciona si el contenedor está arriba) y opcionalmente corremos migraciones
        Start-Process "cmd.exe" -ArgumentList "/k title Backend Docker && cd ""$backendPath"" && docker-compose up -d && echo Generando Key... && docker-compose exec -T laravel.test php artisan key:generate && echo Contenedores en ejecucion!"
    } else {
        Write-Host "[2/4] Iniciando servidor local (PHP Artisan)..." -ForegroundColor Green
        Start-Process "cmd.exe" -ArgumentList "/k title Backend Laravel && cd ""$backendPath"" && php artisan serve"
    }

} else {
    Write-Host "No se encontró el directorio laravel-temp para el Backend." -ForegroundColor Red
}

# --- 2. FRONTEND (Vue) ---
Write-Host "`n[3/4] Verificando Frontend (Vue)..." -ForegroundColor Yellow
$vuePath = Join-Path $projectRoot "vue-frontend"

if (Test-Path $vuePath) {
    Set-Location $vuePath
    
    # Instalar dependencias de NPM
    if (Test-Path "node_modules") {
        Write-Host "Las dependencias de NPM ya están instaladas. Saltando..." -ForegroundColor Green
    } else {
        Write-Host "Instalando dependencias de NPM..." -ForegroundColor Yellow
        npm install
    }
    
    Write-Host "[4/4] Iniciando servidor Vite en una nueva ventana..." -ForegroundColor Green
    Start-Process "cmd.exe" -ArgumentList "/k title Frontend Vue && cd ""$vuePath"" && npm run dev"
} else {
    Write-Host "No se encontró el directorio vue-frontend." -ForegroundColor Red
}

Set-Location $projectRoot

Write-Host "`n=================================================" -ForegroundColor Cyan
Write-Host " ¡Todo Listo!                                    " -ForegroundColor Cyan
Write-Host " Se han abierto ventanas independientes para     " -ForegroundColor Cyan
Write-Host " los servidores del Backend y del Frontend.      " -ForegroundColor Cyan
Write-Host " Puedes minimizar esta ventana o cerrarla.       " -ForegroundColor Cyan
Write-Host "=================================================`n" -ForegroundColor Cyan
