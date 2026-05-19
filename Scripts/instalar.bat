@echo off
REM Script de atajo para ejecutar el instalador PowerShell
REM Este archivo permite ejecutar el instalador con un doble clic

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0install.ps1"
pause

