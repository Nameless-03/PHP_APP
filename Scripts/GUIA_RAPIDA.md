# 🚀 GUÍA RÁPIDA - Instalación del Proyecto

## Paso 1: Ejecutar el Instalador

### Opción A: Doble Clic (Más Fácil)
1. **Haz doble clic** en el archivo `instalar.bat`
2. Se abrirá PowerShell automáticamente
3. Sigue las instrucciones en pantalla

### Opción B: PowerShell (Control Total)
```powershell
# Abre PowerShell y navega a la carpeta del proyecto
cd C:\Users\[tu-usuario]\IdeaProjects\PHP_APP

# Ejecuta el script de instalación
powershell -ExecutionPolicy Bypass -File install.ps1
```

---

## ¿Qué Hace el Script?

El script instalador **automáticamente**:

✅ Verifica PHP, Composer, Git  
✅ Crea un proyecto Laravel 11 nuevo  
✅ Instala todas las dependencias necesarias:
  - LiveKit (videollamadas)
  - OAuth Google (autenticación)
  - PayPal (pagos)
  - Más herramientas útiles

✅ Configura el archivo `.env`  
✅ Te pide credenciales de PostgreSQL y terceros  

---

## Credenciales Que te Pedirá

### 1. Base de Datos PostgreSQL
```
Host: localhost
Puerto: 5432
Usuario: postgres
Contraseña: [tu contraseña]
Base de datos: plataforma_servicios
```

### 2. LiveKit (opcional)
Si tienes servidor LiveKit configurado, proporciona:
- URL del servidor
- API Key
- API Secret

### 3. Google OAuth (opcional)
Desde [Google Cloud Console](https://console.cloud.google.com/):
- Client ID
- Client Secret

### 4. PayPal (opcional)
Desde [PayPal Developer](https://developer.paypal.com/):
- Modo: sandbox (prueba) o live (producción)
- Client ID
- Secret

**Nota**: Puedes saltarte los opcionales y configurarlos luego editando `.env`

---

## Después de la Instalación

### 1. Crear Base de Datos (si aún no existe)
```powershell
cd laravel-app
psql -U postgres -c "CREATE DATABASE plataforma_servicios;"
```

### 2. Usar el Configurador (Recomendado)
```powershell
# Haz doble clic en configurar.bat
# O en PowerShell:
Set-ExecutionPolicy Bypass -Scope Process -Force
.\configure.ps1
```

Este menú te permite:
- Crear base de datos
- Ejecutar migraciones
- Generar datos de prueba
- Iniciar servidor de desarrollo
- Ver configuración actual
- Limpiar cachés

---

## Comandos Rápidos

```powershell
# Navegar al proyecto
cd laravel-app

# Iniciar servidor (también hace: http://localhost:8000)
php artisan serve

# Ejecutar migraciones de BD
php artisan migrate

# Generar datos de prueba
php artisan db:seed

# Ver todas las rutas registradas
php artisan route:list

# Crear nuevo modelo
php artisan make:model MiModelo -m -c

# Ver estado del proyecto
php artisan about
```

---

## ¿Qué está en la Carpeta?

```
PHP_APP/
├── install.ps1              ← EJECUTA ESTO PRIMERO
├── instalar.bat             ← O haz doble clic en esto
├── configure.ps1            ← Para configuración post-instalación
├── configurar.bat           ← O doble clic en esto
├── README_INSTALACION.md    ← Documentación completa
├── GUIA_RAPIDA.md           ← Este archivo
├── laravel-app/             ← Tu proyecto Laravel (se crea)
├── CapaNegocio/             ← Tu lógica de negocio
└── ... otros archivos
```

---

## 🆘 Si Algo Falla

### Error: "PHP no está instalado"
1. Descarga PHP 8.2+ desde https://windows.php.net/download/
2. Instala en `C:\php\` o similar
3. Añade a `PATH` en variables de entorno
4. Reinicia PowerShell

### Error: "Composer no encontrado"
El script intenta descargarlo automáticamente. Si falla:
1. Descarga desde https://getcomposer.org/download/
2. Ejecuta el instalador de Windows

### Error: "Cannot execute scripts"
```powershell
# Usa esto para ejecutar el script:
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process -Force
.\install.ps1
```

### Error: "Database connection failed"
1. Verifica que PostgreSQL está corriendo
2. Comprueba usuario/contraseña en `.env`
3. Crea la BD manualmente si es necesario

---

## 📝 Archivo de Configuración

El script crea `laravel-app/.env` automáticamente. Si necesitas cambiar algo:

```env
# URL de la aplicación
APP_URL=http://localhost:8000

# Base de datos
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
DB_DATABASE=plataforma_servicios

# LiveKit
LIVEKIT_URL=https://livekit.example.com
LIVEKIT_API_KEY=tu_key
LIVEKIT_API_SECRET=tu_secret

# Google OAuth
GOOGLE_CLIENT_ID=tu_client_id
GOOGLE_CLIENT_SECRET=tu_secret

# PayPal
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=tu_client_id
PAYPAL_CLIENT_SECRET=tu_secret
```

---

## 💡 Tips

- Si trabajas en equipo, cada developer corre el script una vez
- No compartas el archivo `.env` en Git (está en `.gitignore`)
- Usa `configure.ps1` para tareas comunes
- Consulta `README_INSTALACION.md` para más detalles

---

## ¿Preguntas?

Consulta `README_INSTALACION.md` para documentación completa.

¡Listo para comenzar! 🎉

