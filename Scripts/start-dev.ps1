$projectRoot = (Get-Item -Path "$PSScriptRoot\..").FullName

Write-Host "=================================================" -ForegroundColor Cyan
Write-Host " Iniciando Entorno de Desarrollo Local           " -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan

# Mover a la raíz del proyecto
Set-Location $projectRoot

$runMigrations = Read-Host "¿Deseas inicializar el proyecto (key:generate, migraciones y seeders)? (Recomendado la primera vez) (s/N)"

# --- 1. BACKEND (Laravel) ---
Write-Host "`n[1/4] Verificando Backend (Laravel/Docker)..." -ForegroundColor Yellow

$backendPath = $projectRoot
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
    
    $migrateCmd = ""
    $dockerMode = "up"
    if ($runMigrations -match "^[sS]") {
        $dockerMode = "up -d"
        $migrateCmd = " && echo Esperando a que la base de datos inicie (15s)... && timeout /t 15 && docker-compose exec laravel.test php artisan key:generate && docker-compose exec laravel.test php artisan migrate --seed && echo Listo, adjuntando logs... && docker-compose logs -f"
    }

    $rebuild = Read-Host "¿Deseas forzar la limpieza y reconstrucción de los contenedores? (s/N)"
    if ($rebuild -match "^[sS]") {
        Write-Host "      Limpiando contenedores antiguos y forzando reconstrucción (Esto tomará varios minutos)..." -ForegroundColor Yellow
        Start-Process "cmd.exe" -ArgumentList "/k title Backend Docker && cd ""$backendPath"" && docker-compose down && docker-compose up --build -d $migrateCmd"
    } else {
        Write-Host "      (Nota: Si es la primera vez, Docker puede tardar entre 5 a 15 minutos descargando la imagen. ¡Paciencia!)" -ForegroundColor Cyan
        Start-Process "cmd.exe" -ArgumentList "/k title Backend Docker && cd ""$backendPath"" && docker-compose $dockerMode $migrateCmd"
    }
} else {
    Write-Host "[2/4] Iniciando servidor local (PHP)..." -ForegroundColor Green
    
    $migrateCmdLocal = ""
    if ($runMigrations -match "^[sS]") {
        $migrateCmdLocal = "php artisan key:generate && php artisan migrate --seed && "
    }
    
    Start-Process "cmd.exe" -ArgumentList "/k title Backend Laravel && cd ""$backendPath"" && $migrateCmdLocal php artisan serve"
}

# --- 2. FRONTEND (Vue) ---
Write-Host "`n[3/4] Verificando Frontend (Vue)..." -ForegroundColor Yellow
$vuePath = Join-Path $projectRoot "vue-frontend"

if (Test-Path $vuePath) {
    Set-Location $vuePath
    
    # Instalar dependencias de NPM
    # Validamos que no solo exista node_modules, sino también los binarios (ej. vite)
    if ((Test-Path "node_modules") -and ((Test-Path "node_modules\.bin\vite") -or (Test-Path "node_modules\.bin\vite.cmd"))) {
        Write-Host "Las dependencias de NPM ya están instaladas correctamente. Saltando..." -ForegroundColor Green
    } else {
        Write-Host "Instalando dependencias de NPM (esto puede tardar un poco)..." -ForegroundColor Yellow
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
