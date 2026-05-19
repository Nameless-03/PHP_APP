#Requires -Version 5.0
<#
.SYNOPSIS
Script de configuración post-instalación para Laravel

.DESCRIPTION
Realiza tareas post-instalación comunes:
- Crear base de datos PostgreSQL
- Ejecutar migraciones
- Generar datos de prueba
- Iniciar servidor de desarrollo

.EXAMPLE
powershell -ExecutionPolicy Bypass -File configure.ps1
#>

# Colores
$colors = @{
    Success = "Green"
    Error = "Red"
    Warning = "Yellow"
    Info = "Cyan"
    Header = "Magenta"
}

function Show-Menu {
    Write-Host ""
    Write-Host "╔════════════════════════════════════════╗" -ForegroundColor $colors.Header
    Write-Host "║  CONFIGURACIÓN POST-INSTALACIÓN       ║" -ForegroundColor $colors.Header
    Write-Host "╚════════════════════════════════════════╝" -ForegroundColor $colors.Header
    Write-Host ""
    Write-Host "1. Crear base de datos PostgreSQL" -ForegroundColor $colors.Info
    Write-Host "2. Ejecutar migraciones" -ForegroundColor $colors.Info
    Write-Host "3. Generar datos de prueba (seeders)" -ForegroundColor $colors.Info
    Write-Host "4. Iniciar servidor de desarrollo" -ForegroundColor $colors.Info
    Write-Host "5. Mostrar configuración actual" -ForegroundColor $colors.Info
    Write-Host "6. Reinstalar dependencias de Composer" -ForegroundColor $colors.Warning
    Write-Host "7. Limpiar cachés del proyecto" -ForegroundColor $colors.Warning
    Write-Host "8. Salir" -ForegroundColor $colors.Error
    Write-Host ""
}

function Create-Database {
    Write-Host ""
    Write-Host "─ CREAR BASE DE DATOS ─" -ForegroundColor $colors.Header
    Write-Host ""

    # Verificar si PostgreSQL está disponible
    if (!(Test-CommandExists "psql")) {
        Write-Host "[✗] PostgreSQL no está instalado o no está en el PATH" -ForegroundColor $colors.Error
        return
    }

    # Leer variables del .env
    $envFile = "laravel-app\.env"
    if (!(Test-Path $envFile)) {
        Write-Host "[✗] No se encontró archivo .env" -ForegroundColor $colors.Error
        return
    }

    $envContent = Get-Content $envFile
    $dbHost = ($envContent | Select-String "DB_HOST=" | Select-Object -First 1).ToString().Split("=")[1]
    $dbName = ($envContent | Select-String "DB_DATABASE=" | Select-Object -First 1).ToString().Split("=")[1]
    $dbUser = ($envContent | Select-String "DB_USERNAME=" | Select-Object -First 1).ToString().Split("=")[1]

    Write-Host "Conexión: $dbUser@$dbHost" -ForegroundColor Gray
    Write-Host "Base de datos: $dbName" -ForegroundColor Gray
    Write-Host ""

    $dbPass = Read-Host "Contraseña de PostgreSQL"

    try {
        $env:PGPASSWORD = $dbPass

        # Crear base de datos
        Write-Host "[→] Creando base de datos: $dbName" -ForegroundColor $colors.Info
        psql -U $dbUser -h $dbHost -c "CREATE DATABASE $dbName;" 2>$null

        if ($LASTEXITCODE -eq 0) {
            Write-Host "[✓] Base de datos creada correctamente" -ForegroundColor $colors.Success
        }
        else {
            Write-Host "[!] La base de datos puede ya existir" -ForegroundColor $colors.Warning
        }

        # Crear extensiones
        Write-Host "[→] Creando extensiones necesarias..." -ForegroundColor $colors.Info
        psql -U $dbUser -h $dbHost -d $dbName -c 'CREATE EXTENSION IF NOT EXISTS "uuid-ossp";' 2>$null
        Write-Host "[✓] Extensiones creadas" -ForegroundColor $colors.Success
    }
    catch {
        Write-Host "[✗] Error al crear base de datos: $_" -ForegroundColor $colors.Error
    }
    finally {
        Remove-Item Env:PGPASSWORD -ErrorAction SilentlyContinue
    }
}

function Run-Migrations {
    Write-Host ""
    Write-Host "─ EJECUTAR MIGRACIONES ─" -ForegroundColor $colors.Header
    Write-Host ""

    $projectPath = "laravel-app"
    if (!(Test-Path $projectPath)) {
        Write-Host "[✗] No se encontró proyecto Laravel" -ForegroundColor $colors.Error
        return
    }

    try {
        Set-Location $projectPath
        Write-Host "[→] Ejecutando migraciones..." -ForegroundColor $colors.Info
        & php artisan migrate

        if ($LASTEXITCODE -eq 0) {
            Write-Host "[✓] Migraciones ejecutadas correctamente" -ForegroundColor $colors.Success
        }
        else {
            Write-Host "[!] Se encontraron algunos errores en las migraciones" -ForegroundColor $colors.Warning
        }
    }
    catch {
        Write-Host "[✗] Error: $_" -ForegroundColor $colors.Error
    }
    finally {
        Set-Location ..
    }
}

function Run-Seeders {
    Write-Host ""
    Write-Host "─ GENERAR DATOS DE PRUEBA ─" -ForegroundColor $colors.Header
    Write-Host ""

    $projectPath = "laravel-app"
    if (!(Test-Path $projectPath)) {
        Write-Host "[✗] No se encontró proyecto Laravel" -ForegroundColor $colors.Error
        return
    }

    try {
        Set-Location $projectPath
        Write-Host "[→] Ejecutando seeders..." -ForegroundColor $colors.Info
        & php artisan db:seed

        if ($LASTEXITCODE -eq 0) {
            Write-Host "[✓] Datos de prueba generados correctamente" -ForegroundColor $colors.Success
        }
        else {
            Write-Host "[!] Se encontraron algunos errores" -ForegroundColor $colors.Warning
        }
    }
    catch {
        Write-Host "[✗] Error: $_" -ForegroundColor $colors.Error
    }
    finally {
        Set-Location ..
    }
}

function Start-Server {
    Write-Host ""
    Write-Host "─ INICIAR SERVIDOR DE DESARROLLO ─" -ForegroundColor $colors.Header
    Write-Host ""

    $projectPath = "laravel-app"
    if (!(Test-Path $projectPath)) {
        Write-Host "[✗] No se encontró proyecto Laravel" -ForegroundColor $colors.Error
        return
    }

    try {
        Set-Location $projectPath
        Write-Host "[→] Iniciando servidor de desarrollo..." -ForegroundColor $colors.Info
        Write-Host "[i] El servidor estará disponible en: http://localhost:8000" -ForegroundColor Gray
        Write-Host "[i] Presiona Ctrl+C para detenerlo" -ForegroundColor Gray
        Write-Host ""

        & php artisan serve
    }
    catch {
        Write-Host "[✗] Error: $_" -ForegroundColor $colors.Error
    }
    finally {
        Set-Location ..
    }
}

function Show-Configuration {
    Write-Host ""
    Write-Host "─ CONFIGURACIÓN ACTUAL ─" -ForegroundColor $colors.Header
    Write-Host ""

    $envFile = "laravel-app\.env"
    if (!(Test-Path $envFile)) {
        Write-Host "[✗] No se encontró archivo .env" -ForegroundColor $colors.Error
        return
    }

    $envContent = Get-Content $envFile

    Write-Host "APLICACIÓN" -ForegroundColor $colors.Info
    Write-Host "  Nombre: $(($envContent | Select-String '^APP_NAME=' | Select-Object -First 1).ToString().Split('=')[1])" -ForegroundColor Gray
    Write-Host "  URL: $(($envContent | Select-String '^APP_URL=' | Select-Object -First 1).ToString().Split('=')[1])" -ForegroundColor Gray
    Write-Host "  Debug: $(($envContent | Select-String '^APP_DEBUG=' | Select-Object -First 1).ToString().Split('=')[1])" -ForegroundColor Gray

    Write-Host ""
    Write-Host "BASE DE DATOS" -ForegroundColor $colors.Info
    Write-Host "  Motor: $(($envContent | Select-String '^DB_CONNECTION=' | Select-Object -First 1).ToString().Split('=')[1])" -ForegroundColor Gray
    Write-Host "  Host: $(($envContent | Select-String '^DB_HOST=' | Select-Object -First 1).ToString().Split('=')[1])" -ForegroundColor Gray
    Write-Host "  Puerto: $(($envContent | Select-String '^DB_PORT=' | Select-Object -First 1).ToString().Split('=')[1])" -ForegroundColor Gray
    Write-Host "  Base de datos: $(($envContent | Select-String '^DB_DATABASE=' | Select-Object -First 1).ToString().Split('=')[1])" -ForegroundColor Gray
    Write-Host "  Usuario: $(($envContent | Select-String '^DB_USERNAME=' | Select-Object -First 1).ToString().Split('=')[1])" -ForegroundColor Gray

    Write-Host ""
    Write-Host "SERVICIOS EXTERNOS" -ForegroundColor $colors.Info

    $livekit = ($envContent | Select-String '^LIVEKIT_URL=' | Select-Object -First 1).ToString().Split('=')[1]
    $google = ($envContent | Select-String '^GOOGLE_CLIENT_ID=' | Select-Object -First 1).ToString().Split('=')[1]
    $paypal = ($envContent | Select-String '^PAYPAL_MODE=' | Select-Object -First 1).ToString().Split('=')[1]

    if ($livekit) { Write-Host "  LiveKit: Configurado ✓" -ForegroundColor $colors.Success }
    else { Write-Host "  LiveKit: No configurado" -ForegroundColor Gray }

    if ($google) { Write-Host "  Google OAuth: Configurado ✓" -ForegroundColor $colors.Success }
    else { Write-Host "  Google OAuth: No configurado" -ForegroundColor Gray }

    if ($paypal) { Write-Host "  PayPal: Configurado ($paypal)" -ForegroundColor $colors.Success }
    else { Write-Host "  PayPal: No configurado" -ForegroundColor Gray }
}

function Reinstall-Dependencies {
    Write-Host ""
    Write-Host "─ REINSTALAR DEPENDENCIAS ─" -ForegroundColor $colors.Header
    Write-Host ""

    $projectPath = "laravel-app"
    if (!(Test-Path $projectPath)) {
        Write-Host "[✗] No se encontró proyecto Laravel" -ForegroundColor $colors.Error
        return
    }

    Write-Host "[!] Esto eliminará y reinstalará todas las dependencias de Composer" -ForegroundColor $colors.Warning
    $confirm = Read-Host "¿Estás seguro? (s/n)"

    if ($confirm -ne "s" -and $confirm -ne "S") {
        Write-Host "Cancelado" -ForegroundColor $colors.Info
        return
    }

    try {
        Set-Location $projectPath

        Write-Host "[→] Eliminando carpeta vendor..." -ForegroundColor $colors.Info
        Remove-Item -Path "vendor" -Recurse -Force -ErrorAction SilentlyContinue

        Write-Host "[→] Actualizando composer..." -ForegroundColor $colors.Info
        & composer update

        if ($LASTEXITCODE -eq 0) {
            Write-Host "[✓] Dependencias reinstaladas correctamente" -ForegroundColor $colors.Success
        }
    }
    catch {
        Write-Host "[✗] Error: $_" -ForegroundColor $colors.Error
    }
    finally {
        Set-Location ..
    }
}

function Clear-Caches {
    Write-Host ""
    Write-Host "─ LIMPIAR CACHÉS ─" -ForegroundColor $colors.Header
    Write-Host ""

    $projectPath = "laravel-app"
    if (!(Test-Path $projectPath)) {
        Write-Host "[✗] No se encontró proyecto Laravel" -ForegroundColor $colors.Error
        return
    }

    try {
        Set-Location $projectPath

        Write-Host "[→] Limpiando caché de configuración..." -ForegroundColor $colors.Info
        & php artisan config:clear

        Write-Host "[→] Limpiando caché de aplicación..." -ForegroundColor $colors.Info
        & php artisan cache:clear

        Write-Host "[→] Limpiando caché de vista..." -ForegroundColor $colors.Info
        & php artisan view:clear

        Write-Host "[→] Limpiando auto-load de Composer..." -ForegroundColor $colors.Info
        & composer dump-autoload

        Write-Host "[✓] Cachés limpiados correctamente" -ForegroundColor $colors.Success
    }
    catch {
        Write-Host "[✗] Error: $_" -ForegroundColor $colors.Error
    }
    finally {
        Set-Location ..
    }
}

function Test-CommandExists {
    param([string]$Command)
    try {
        if (Get-Command $Command -ErrorAction Stop) { return $true }
    }
    catch { return $false }
}

# Menú principal
Clear-Host
Show-Menu

while ($true) {
    $opcion = Read-Host "Selecciona una opción (1-8)"

    switch ($opcion) {
        "1" { Create-Database }
        "2" { Run-Migrations }
        "3" { Run-Seeders }
        "4" { Start-Server; break }
        "5" { Show-Configuration }
        "6" { Reinstall-Dependencies }
        "7" { Clear-Caches }
        "8" { Write-Host "Hasta luego!" -ForegroundColor $colors.Success; exit }
        default { Write-Host "Opción no válida" -ForegroundColor $colors.Error }
    }

    if ($opcion -ne "4") {
        Write-Host ""
        Read-Host "Presiona Enter para continuar"
        Clear-Host
        Show-Menu
    }
}

