@echo off
title Lanzador de Proyecto
echo ==============================================
echo Iniciando scripts de configuracion y despliegue...
echo ==============================================
echo.

PowerShell.exe -NoProfile -ExecutionPolicy Bypass -Command "& '%~dp0start-dev.ps1'"

pause
