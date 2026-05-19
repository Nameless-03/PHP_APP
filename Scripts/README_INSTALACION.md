# Instalador Automático - Plataforma de Gestión de Servicios

Script PowerShell profesional que instala y configura automáticamente todo lo necesario para el proyecto Laravel con:
- **PHP 8.2+**
- **Composer**
- **Laravel 11**
- **PostgreSQL**
- **LiveKit** (videollamadas)
- **OAuth Google** (autenticación)
- **PayPal** (pagos)

---

## 🚀 Requisitos Mínimos

- **Windows 10** o superior
- **PowerShell 5.0** o superior
- **5 GB** de espacio libre en disco
- **Conexión a Internet**

### Verificar tu versión de Windows y PowerShell

```powershell
# Abre PowerShell y ejecuta:
$PSVersionTable.PSVersion  # Debe ser 5.0 o superior
[Environment]::OSVersion.Version  # Debe ser Windows 10 o posterior
```

---

## 📥 Instalación Rápida

### Opción 1: Doble Clic (Recomendado)
1. Haz doble clic en **`instalar.bat`**
2. Lee las instrucciones en pantalla
3. Sigue los pasos interactivos

### Opción 2: Línea de Comandos
```powershell
# Abre PowerShell como Administrador
cd C:\Users\[tu-usuario]\IdeaProjects\PHP_APP
powershell -ExecutionPolicy Bypass -File install.ps1
```

### Opción 3: PowerShell (Sin cambiar ejecución global)
```powershell
# En PowerShell standard:
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process -Force
Set-Location C:\Users\[tu-usuario]\IdeaProjects\PHP_APP
.\install.ps1
```

---

## 📋 ¿Qué Hace el Script?

### Paso 1: Verificar Requisitos
- Valida versión de Windows y PowerShell
- Verifica espacio disponible en disco
- Comprueba dependencias del sistema

### Paso 2: Verificar/Instalar PHP
- Busca PHP instalado
- Si no está, te guía para descargarlo
- Valida versión 8.2+

### Paso 3: Verificar/Instalar Composer
- Comprueba si Composer está disponible
- Si no está, lo descarga e instala automáticamente
- Valida la instalación

### Paso 4: Verificar/Instalar Git
- Necesario para algunos paquetes de Composer
- Es opcional pero recomendado

### Paso 5: Crear Proyecto Laravel
- Crea nuevo proyecto Laravel 11
- O usa instalación existente

### Paso 6: Instalar Dependencias
```
✓ Laravel Sanctum (Autenticación API)
✓ LiveKit Server SDK (Videollamadas)
✓ Guzzle HTTP Client (APIs externas)
✓ Laravel Socialite (OAuth Google)
✓ PayPal SDK (Pagos)
✓ Predis (Redis Client)
✓ Laravel Queue (Jobs)
✓ CORS (Control de acceso)
```

### Paso 7: Configurar Variables de Entorno
- Crea archivo `.env`
- Solicita datos de PostgreSQL
- Configura credenciales opcionales (LiveKit, Google, PayPal)

---

## 🔧 Configuración de Credenciales

El script te pedirá datos para:

### Base de Datos PostgreSQL
```
Host: localhost (o tu servidor)
Puerto: 5432
Usuario: postgres
Contraseña: [tu contraseña]
Base de datos: plataforma_servicios
```

### LiveKit (Opcional)
- URL del servidor
- API Key
- API Secret

### Google OAuth (Opcional)
- Client ID (obtenido de Google Cloud Console)
- Client Secret

### PayPal (Opcional)
- Modo: sandbox o live
- Client ID
- Secret

**Puedes configurarlo después editando el archivo `.env`**

---

## ✅ Verificación Post-instalación

Después de ejecutar el script, verifica que todo esté correcto:

```powershell
# Navega al proyecto
cd laravel-app

# Verifica PHP
php --version

# Verifica Composer
composer --version

# Verifica que Laravel está instalado
php artisan --version
```

---

## 🌐 Próximos Pasos

### 1. Crear Base de Datos PostgreSQL
```powershell
# Si aún no la has creado:
createdb -U postgres -h localhost plataforma_servicios

# O usando psql:
psql -U postgres
CREATE DATABASE plataforma_servicios;
\q
```

### 2. Ejecutar Migraciones
```powershell
cd laravel-app
php artisan migrate
```

### 3. (Opcional) Generar Datos de Prueba
```powershell
php artisan db:seed
```

### 4. Iniciar Servidor de Desarrollo
```powershell
php artisan serve
```

La aplicación estará disponible en: **http://localhost:8000**

---

## 📁 Estructura de Carpetas

Después de la instalación, tu estructura será:

```
PHP_APP/
├── laravel-app/                 # Tu proyecto Laravel
│   ├── app/                      # Clases de aplicación
│   ├── config/                   # Archivos de configuración
│   ├── database/                 # Migraciones y seeders
│   ├── routes/                   # Rutas de la aplicación
│   ├── storage/                  # Archivos temporales
│   ├── .env                      # Variables de entorno
│   └── artisan                   # CLI de Laravel
├── CapaNegocio/                  # Tu lógica de negocio
├── CapaFrontend/                 # Frontend (si lo hay)
├── install.ps1                   # Este script
└── instalar.bat                  # Atajo para Windows
```

---

## 🐛 Solución de Problemas

### Error: "PHP no está instalado"
**Solución:**
1. Descarga PHP desde: https://windows.php.net/download/
2. Selecciona la versión "Thread Safe" o "Non Thread Safe" (más reciente)
3. Descomprime en `C:\php` o similar
4. Añade a la variable de entorno PATH: `C:\php`
5. Reinicia PowerShell y ejecuta el script nuevamente

### Error: "Composer no está instalado"
**Solución:**
1. El script intentará descargarlo automáticamente
2. Si falla, descarga manualmente desde: https://getcomposer.org/download/
3. Ejecuta el instalador de Windows que descarques

### Error: "git not found"
**Solución:**
1. Descarga Git desde: https://git-scm.com/download/win
2. Instala con opciones por defecto
3. Reinicia PowerShell

### Error: "No se puede acceder a base de datos"
**Solución:**
1. Verifica que PostgreSQL está instalado: `psql --version`
2. Verifica credenciales en `.env`
3. Comprueba que PostgreSQL en en ejecución
4. Verifica que la base de datos existe: `psql -U postgres -l`

### Error: "Cannot execute scripts"
**Solución:**
Esta es una limitación de ejecución de PowerShell. Usa una de estas opciones:

Opción A - Cambiar política de ejecución temporalmente:
```powershell
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process -Force
.\install.ps1
```

Opción B - Usar el archivo `.bat`:
```
Haz doble clic en instalar.bat
```

---

## 📞 Contacto y Soporte

Si tienes problemas:

1. **Verifica los requisitos** (PHP 8.2+, Windows 10+)
2. **Lee los mensajes de error** con atención
3. **Usa Google** para buscar el error específico
4. **Contacta al equipo de desarrollo** con:
   - Captura de pantalla del error
   - Versión de Windows y PowerShell
   - Paso donde ocurre el error

---

## 🔐 Notas de Seguridad

⚠️ **IMPORTANTE:**
- Nunca compartas credenciales en repositorios Git
- El archivo `.env` está en `.gitignore` por razones de seguridad
- Usa variables de entorno diferentes para desarrollo y producción
- Los datos de PayPal y Google deben guardarse de forma segura

---

## 📝 Archivo de Configuración (.env)

Después de instalar, edita `laravel-app/.env`:

```env
APP_NAME="Plataforma de Servicios"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=plataforma_servicios
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña

# LiveKit
LIVEKIT_URL=https://livekit.example.com
LIVEKIT_API_KEY=tu_api_key
LIVEKIT_API_SECRET=tu_api_secret

# Google OAuth
GOOGLE_CLIENT_ID=tu_client_id
GOOGLE_CLIENT_SECRET=tu_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# PayPal
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=tu_client_id
PAYPAL_CLIENT_SECRET=tu_secret
```

---

## 🚀 Comandos Útiles Después de Instalar

```powershell
# Iniciar servidor
php artisan serve

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Crear nuevos modelos
php artisan make:model NombreModelo -m

# Crear controladores
php artisan make:controller NombreControlador

# Ver rutas registradas
php artisan route:list

# Cachear configuración
php artisan config:cache

# Mostrar información de la aplicación
php artisan about
```

---

## 📚 Documentación Útil

- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [PHP Official](https://www.php.net/)
- [PostgreSQL](https://www.postgresql.org/docs/)
- [Composer](https://getcomposer.org/doc/)
- [LiveKit Docs](https://docs.livekit.io/)
- [Google OAuth](https://developers.google.com/identity/protocols/oauth2)
- [PayPal API](https://developer.paypal.com/docs/)

---

## ¿Preguntas?

Este script fue creado para simplificar la instalación. Si tienes dudas sobre qué hace cada parte, lee los comentarios en el código PowerShell o consulta la documentación oficial de los componentes.

¡Buena suerte con tu proyecto! 🎉

