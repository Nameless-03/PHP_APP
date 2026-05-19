#Requires -Version 5.0
<#
.SYNOPSIS
Script de instalación completa para Plataforma de Gestión de Servicios (Laravel 11)

.DESCRIPTION
Este script instala y configura automáticamente:
- PHP 8.2+
- Composer
- Laravel 11
- PostgreSQL
- Dependencias: LiveKit, OAuth Google, PayPal
- Variables de entorno

.EXAMPLE
powershell -ExecutionPolicy Bypass -File install.ps1

.AUTHOR
Equipo de Desarrollo

.VERSION
1.0
#>

param(
    [switch]$SkipDatabase = $false,
    [switch]$SkipBrowser = $false
)

# Configuración de colores
$colors = @{
    Success = "Green"
    Error = "Red"
    Warning = "Yellow"
    Info = "Cyan"
    Header = "Magenta"
}

# Función para mostrar encabezados
function Show-Header {
    param([string]$Text)
    Write-Host "`n" -NoNewline
    Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor $colors.Header
    Write-Host "║ $($Text.PadRight(58)) ║" -ForegroundColor $colors.Header
    Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor $colors.Header
    Write-Host ""
}

# Función para mostrar mensajes de éxito
function Show-Success {
    param([string]$Message)
    Write-Host "[✓] " -ForegroundColor $colors.Success -NoNewline
    Write-Host $Message
}

# Función para mostrar errores
function Show-Error {
    param([string]$Message)
    Write-Host "[✗] " -ForegroundColor $colors.Error -NoNewline
    Write-Host $Message
}

# Función para mostrar advertencias
function Show-Warning {
    param([string]$Message)
    Write-Host "[!] " -ForegroundColor $colors.Warning -NoNewline
    Write-Host $Message
}

# Función para mostrar información
function Show-Info {
    param([string]$Message)
    Write-Host "[→] " -ForegroundColor $colors.Info -NoNewline
    Write-Host $Message
}

# Función para verificar si un comando existe
function Test-CommandExists {
    param([string]$Command)
    try {
        if (Get-Command $Command -ErrorAction Stop) {
            return $true
        }
    }
    catch {
        return $false
    }
}

# Función para ejecutar comandos con manejo de errores
function Invoke-SafeCommand {
    param(
        [string]$Command,
        [string]$Description,
        [switch]$Critical
    )

    Show-Info $Description
    try {
        Invoke-Expression $Command | Out-Null
        Show-Success "$Description completado"
        return $true
    }
    catch {
        Show-Error "Error: $_"
        if ($Critical) {
            Write-Host "`nEl script se detuvo debido a un error crítico." -ForegroundColor $colors.Error
            exit 1
        }
        return $false
    }
}

# ============================================================================
# INICIO DEL SCRIPT
# ============================================================================

Clear-Host
Show-Header "INSTALADOR - PLATAFORMA GESTIÓN DE SERVICIOS"

Write-Host "Este script instalará y configurará automáticamente:" -ForegroundColor $colors.Info
Write-Host "  • PHP 8.2+"
Write-Host "  • Composer"
Write-Host "  • Laravel 11"
Write-Host "  • PostgreSQL (opcional)"
Write-Host "  • LiveKit SDK"
Write-Host "  • OAuth Google"
Write-Host "  • PayPal API"
Write-Host ""

$continuar = Read-Host "¿Deseas continuar? (s/n)"
if ($continuar -ne "s" -and $continuar -ne "S") {
    Write-Host "`nInstalación cancelada." -ForegroundColor $colors.Warning
    exit 0
}

# ============================================================================
# PASO 1: VERIFICAR REQUISITOS
# ============================================================================

Show-Header "PASO 1/7 - VERIFICANDO REQUISITOS"

# Verificar Windows 10+
$osVersion = [Environment]::OSVersion.Version
if ($osVersion.Major -lt 10) {
    Show-Error "Se requiere Windows 10 o posterior"
    exit 1
}
Show-Success "Windows versión: $($osVersion.Major).$($osVersion.Minor)"

# Verificar PowerShell 5.0+
if ($PSVersionTable.PSVersion.Major -lt 5) {
    Show-Error "Se requiere PowerShell 5.0 o posterior"
    exit 1
}
Show-Success "PowerShell versión: $($PSVersionTable.PSVersion.Major).$($PSVersionTable.PSVersion.Minor)"

# Verificar espacio en disco
$disk = Get-Volume -DriveLetter C
$espacioLibreGB = [math]::Round($disk.SizeRemaining / 1GB, 2)
if ($espacioLibreGB -lt 5) {
    Show-Warning "Solo hay $espacioLibreGB GB libres. Se recomienda al menos 5 GB"
}
else {
    Show-Success "Espacio disponible: $espacioLibreGB GB"
}

# ============================================================================
# PASO 2: VERIFICAR/INSTALAR PHP
# ============================================================================

Show-Header "PASO 2/7 - VERIFICANDO PHP"

if (Test-CommandExists "php") {
    $phpVersion = php -v | Select-Object -First 1
    Show-Success "PHP ya está instalado"
    Write-Host "  $phpVersion" -ForegroundColor Gray
}
else {
    Show-Warning "PHP no está instalado"
    Write-Host "`nOpciones de instalación:" -ForegroundColor $colors.Info
    Write-Host "  1. Descarga manual desde: https://windows.php.net/download/"
    Write-Host "  2. Usa Chocolatey: choco install php"
    Write-Host "  3. Usa Scoop: scoop install php"
    Write-Host ""

    $instalar = Read-Host "¿Quieres descargar la página de PHP? (s/n)"
    if ($instalar -eq "s" -o $instalar -eq "S") {
        Start-Process "https://windows.php.net/download/"
    }

    Show-Warning "Por favor instala PHP 8.2+ y ejecuta este script nuevamente"
    exit 1
}

# ============================================================================
# PASO 3: VERIFICAR/INSTALAR COMPOSER
# ============================================================================

Show-Header "PASO 3/7 - VERIFICANDO COMPOSER"

if (Test-CommandExists "composer") {
    $composerVersion = composer --version
    Show-Success "Composer ya está instalado"
    Write-Host "  $composerVersion" -ForegroundColor Gray
    $composerCmd = "composer"
}
else {
    Show-Warning "Composer no está instalado"
    Show-Info "Descargando Composer..."

    $downloadsPath = "$env:USERPROFILE\Downloads"
    $installerPath = "$downloadsPath\Composer-Setup.exe"

    try {
        $ProgressPreference = 'SilentlyContinue'
        Invoke-WebRequest -Uri "https://getcomposer.org/Composer-Setup.exe" -OutFile $installerPath

        Show-Info "Ejecutando instalador de Composer..."
        Start-Process -FilePath $installerPath -Wait

        if (Test-CommandExists "composer") {
            Show-Success "Composer instalado correctamente"
            $composerCmd = "composer"
        }
        else {
            Show-Error "No se pudo instalar Composer"
            Show-Warning "Descárgalo manualmente desde: https://getcomposer.org/download/"
            exit 1
        }
    }
    catch {
        Show-Error "Error al descargar Composer: $_"
        Show-Warning "Descárgalo manualmente desde: https://getcomposer.org/download/"
        exit 1
    }
}

# ============================================================================
# PASO 4: VERIFICAR/INSTALAR GIT
# ============================================================================

Show-Header "PASO 4/7 - VERIFICANDO GIT"

if (Test-CommandExists "git") {
    $gitVersion = git --version
    Show-Success "Git ya está instalado"
    Write-Host "  $gitVersion" -ForegroundColor Gray
}
else {
    Show-Warning "Git no está instalado"
    Show-Info "Git es necesario para algunos paquetes de Composer"

    $instalarGit = Read-Host "¿Deseas instalar Git? (s/n)"
    if ($instalarGit -eq "s" -o $instalarGit -eq "S") {
        Show-Info "Abriendo página de descarga de Git..."
        Start-Process "https://git-scm.com/download/win"
        Write-Host "`nPor favor instala Git y ejecuta este script nuevamente" -ForegroundColor $colors.Warning
        exit 1
    }
}

# ============================================================================
# PASO 5: CREAR PROYECTO LARAVEL
# ============================================================================

Show-Header "PASO 5/7 - CREANDO PROYECTO LARAVEL 11"

$projectPath = Join-Path (Get-Location) "laravel-app"

if (Test-Path $projectPath) {
    Show-Warning "Ya existe una carpeta 'laravel-app'"
    $reinstalar = Read-Host "¿Deseas reinstalarlo? (s/n)"
    if ($reinstalar -eq "s" -o $reinstalar -eq "S") {
        Show-Info "Eliminando carpeta existente..."
        Remove-Item -Path $projectPath -Recurse -Force
    }
    else {
        Show-Info "Usando instalación existente"
    }
}

if (!(Test-Path $projectPath)) {
    Show-Info "Creando proyecto Laravel 11..."
    try {
        & $composerCmd create-project laravel/laravel laravel-app
        if ($LASTEXITCODE -eq 0) {
            Show-Success "Proyecto Laravel 11 creado correctamente"
        }
        else {
            Show-Error "Error al crear proyecto Laravel"
            exit 1
        }
    }
    catch {
        Show-Error "Error: $_"
        exit 1
    }
}

# Cambiar a directorio del proyecto
Set-Location $projectPath

# ============================================================================
# PASO 6: INSTALAR DEPENDENCIAS
# ============================================================================

Show-Header "PASO 6/7 - INSTALANDO DEPENDENCIAS"

$dependencias = @(
    @{
        nombre = "Laravel Sanctum (Autenticación API)"
        comando = "require laravel/sanctum"
    },
    @{
        nombre = "LiveKit Server SDK (Videollamadas)"
        comando = "require agence104/livekit-server-sdk"
    },
    @{
        nombre = "Guzzle HTTP Client (APIs externas)"
        comando = "require guzzlehttp/guzzle"
    },
    @{
        nombre = "Laravel Socialite (OAuth Google)"
        comando = "require laravel/socialite"
    },
    @{
        nombre = "PayPal SDK (Pagos)"
        comando = "require paypalrestsdk"
    },
    @{
        nombre = "Predis (Redis Client)"
        comando = "require predis/predis"
    },
    @{
        nombre = "Laravel Queue (Jobs)"
        comando = "require laravel/queue"
    },
    @{
        nombre = "CORS (Control de acceso)"
        comando = "require fruitcake/laravel-cors"
    }
)

foreach ($dep in $dependencias) {
    Show-Info "Instalando: $($dep.nombre)..."
    try {
        & $composerCmd $($dep.comando.Split()) | Out-Null
        Show-Success "$($dep.nombre) instalado"
    }
    catch {
        Show-Warning "Error al instalar $($dep.nombre): $_"
    }
}

# ============================================================================
# PASO 7: CONFIGURAR VARIABLES DE ENTORNO
# ============================================================================

Show-Header "PASO 7/7 - CONFIGURANDO AMBIENTE"

# Crear .env si no existe
if (!(Test-Path ".env")) {
    Show-Info "Creando archivo .env..."
    Copy-Item ".env.example" ".env"
    Show-Success "Archivo .env creado"
}

# Generar APP_KEY
Show-Info "Generando clave de aplicación..."
& php artisan key:generate --quiet
Show-Success "Clave de aplicación generada"

# Configurar variables de base de datos
Show-Header "CONFIGURACIÓN DE BASE DE DATOS"

Write-Host "Por favor proporciona los datos de conexión a PostgreSQL:" -ForegroundColor $colors.Info
Write-Host ""

$dbHost = Read-Host "Host database (ej: localhost, 127.0.0.1)"
$dbPort = Read-Host "Puerto (ej: 5432)"
$dbName = Read-Host "Nombre de la base de datos (ej: plataforma_servicios)"
$dbUser = Read-Host "Usuario PostgreSQL (ej: postgres)"
$dbPass = Read-Host "Contraseña PostgreSQL" -AsSecureString
$dbPassPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToCoTaskMemUnicode($dbPass))

# Actualizar .env
(Get-Content ".env") -replace 'DB_DRIVER=.*', 'DB_DRIVER=pgsql' | Set-Content ".env"
(Get-Content ".env") -replace 'DB_HOST=.*', "DB_HOST=$dbHost" | Set-Content ".env"
(Get-Content ".env") -replace 'DB_PORT=.*', "DB_PORT=$dbPort" | Set-Content ".env"
(Get-Content ".env") -replace 'DB_DATABASE=.*', "DB_DATABASE=$dbName" | Set-Content ".env"
(Get-Content ".env") -replace 'DB_USERNAME=.*', "DB_USERNAME=$dbUser" | Set-Content ".env"
(Get-Content ".env") -replace 'DB_PASSWORD=.*', "DB_PASSWORD=$dbPassPlain" | Set-Content ".env"

Show-Success "Variables de base de datos configuradas"

# Añadir variables de LiveKit
Show-Header "CONFIGURACIÓN DE APIKEYS OPCIONALES"

Write-Host "Opcionalmente puedes configurar las credenciales de terceros ahora." -ForegroundColor $colors.Info
Write-Host "Si prefieres, puedes hacerlo después editando el archivo .env" -ForegroundColor Gray
Write-Host ""

$configLiveKit = Read-Host "¿Deseas configurar LiveKit ahora? (s/n)"
if ($configLiveKit -eq "s" -o $configLiveKit -eq "S") {
    $liveKitUrl = Read-Host "LiveKit Server URL (ej: https://livekit.example.com)"
    $liveKitApiKey = Read-Host "LiveKit API Key"
    $liveKitApiSecret = Read-Host "LiveKit API Secret" -AsSecureString
    $liveKitApiSecretPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToCoTaskMemUnicode($liveKitApiSecret))

    Add-Content ".env" "`nLIVEKIT_URL=$liveKitUrl"
    Add-Content ".env" "LIVEKIT_API_KEY=$liveKitApiKey"
    Add-Content ".env" "LIVEKIT_API_SECRET=$liveKitApiSecretPlain"

    Show-Success "LiveKit configurado"
}

$configGoogle = Read-Host "¿Deseas configurar Google OAuth ahora? (s/n)"
if ($configGoogle -eq "s" -o $configGoogle -eq "S") {
    $googleClientId = Read-Host "Google Client ID"
    $googleClientSecret = Read-Host "Google Client Secret" -AsSecureString
    $googleClientSecretPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToCoTaskMemUnicode($googleClientSecret))

    Add-Content ".env" "`nGOOGLE_CLIENT_ID=$googleClientId"
    Add-Content ".env" "GOOGLE_CLIENT_SECRET=$googleClientSecretPlain"
    Add-Content ".env" "GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback"

    Show-Success "Google OAuth configurado"
}

$configPayPal = Read-Host "¿Deseas configurar PayPal ahora? (s/n)"
if ($configPayPal -eq "s" -o $configPayPal -eq "S") {
    $paypalMode = Read-Host "Modo PayPal (sandbox/live)"
    $paypalClientId = Read-Host "PayPal Client ID"
    $paypalSecret = Read-Host "PayPal Secret" -AsSecureString
    $paypalSecretPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToCoTaskMemUnicode($paypalSecret))

    Add-Content ".env" "`nPAYPAL_MODE=$paypalMode"
    Add-Content ".env" "PAYPAL_CLIENT_ID=$paypalClientId"
    Add-Content ".env" "PAYPAL_CLIENT_SECRET=$paypalSecretPlain"

    Show-Success "PayPal configurado"
}

# ============================================================================
# RESUMEN Y PRÓXIMOS PASOS
# ============================================================================

Show-Header "INSTALACIÓN COMPLETADA"

Write-Host ""
Write-Host "Tu proyecto Laravel está listo. Ahora necesitas:" -ForegroundColor $colors.Success
Write-Host ""
Write-Host "1. Crear la base de datos PostgreSQL:" -ForegroundColor $colors.Info
Write-Host "   createdb -U $dbUser -h $dbHost $dbName" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Ejecutar las migraciones:" -ForegroundColor $colors.Info
Write-Host "   php artisan migrate" -ForegroundColor Gray
Write-Host ""
Write-Host "3. (Opcional) Generar datos de prueba:" -ForegroundColor $colors.Info
Write-Host "   php artisan db:seed" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Iniciar el servidor de desarrollo:" -ForegroundColor $colors.Info
Write-Host "   php artisan serve" -ForegroundColor Gray
Write-Host ""
Write-Host "Accede a: http://localhost:8000" -ForegroundColor $colors.Success
Write-Host ""

# Ofrecer opciones finales
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $colors.Header
Write-Host ""

$abrirExplorer = Read-Host "¿Deseas abrir la carpeta del proyecto? (s/n)"
if ($abrirExplorer -eq "s" -o $abrirExplorer -eq "S") {
    Invoke-Item (Get-Location)
}

$abrirEditor = Read-Host "¿Deseas abrir VS Code en el proyecto? (s/n)"
if ($abrirEditor -eq "s" -o $abrirEditor -eq "S") {
    if (Test-CommandExists "code") {
        & code .
    }
    else {
        Show-Warning "VS Code no está instalado"
    }
}

Write-Host ""
Write-Host "Instalación finalizada. ¡Éxito!" -ForegroundColor $colors.Success
Write-Host ""

Read-Host 'Presiona Enter para cerrar esta ventana'
