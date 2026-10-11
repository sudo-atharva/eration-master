@echo off
rem Demo mode: SQLite (demo.sqlite) via php/db_shim.php, no MySQL needed. Run install.bat once first.
cd /d "%~dp0"
set "PHPDIR=%~dp0runtime\php"
if not exist "%PHPDIR%\php.exe" set "PHPDIR=%~dp0..\runtime\php"
if not exist "%PHPDIR%\php.exe" (
  echo PHP not found. Run install.bat first.
  pause
  exit /b 1
)
start "" http://localhost:8000
"%PHPDIR%\php.exe" -c "%PHPDIR%\php.ini" -S 0.0.0.0:8000 -t "%~dp0."
