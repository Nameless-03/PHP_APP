@echo off
REM Script de atajo para ejecutar la configuración post-instalación

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0configure.ps1"
pause

