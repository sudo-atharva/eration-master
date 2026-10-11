@echo off
rem E-Ration one-click setup: downloads portable PHP (no XAMPP/MySQL needed), then starts the site.
rem Uses the bundled SQLite database (demo.sqlite). Needs Windows 10+ (built-in curl and tar) and internet once.
setlocal
cd /d "%~dp0"
set "PHPDIR=%~dp0runtime\php"
set "PHPZIP=https://windows.php.net/downloads/releases/latest/php-8.3-nts-Win32-vs16-x64-latest.zip"
rem Windows' own curl/tar: a Git or MSYS tar on PATH can't read "C:\..." paths.
set "CURL=%SystemRoot%\System32\curl.exe"
set "TAR=%SystemRoot%\System32\tar.exe"

if exist "%PHPDIR%\php.exe" goto ini
echo [1/3] Downloading PHP 8.3 ...
mkdir "%PHPDIR%" 2>nul
"%CURL%" -L --fail -o "%TEMP%\eration-php.zip" "%PHPZIP%" || goto fail
"%TAR%" -xf "%TEMP%\eration-php.zip" -C "%PHPDIR%" || goto fail
del "%TEMP%\eration-php.zip"

:ini
echo [2/3] Writing php.ini ...
(
  echo extension_dir = "%PHPDIR%\ext"
  echo extension=gd
  echo extension=mbstring
  echo extension=openssl
  echo extension=pdo_sqlite
  echo display_errors = Off
  echo upload_max_filesize = 8M
) > "%PHPDIR%\php.ini"

"%PHPDIR%\php.exe" -c "%PHPDIR%\php.ini" -r "exit(extension_loaded('pdo_sqlite') ? 0 : 1);"
if errorlevel 1 (
  echo PHP did not start. Installing the Microsoft Visual C++ runtime it needs...
  winget install -e --id Microsoft.VCRedist.2015+.x64 --accept-package-agreements --accept-source-agreements
  "%PHPDIR%\php.exe" -c "%PHPDIR%\php.ini" -v || goto fail
)

rem Optional: ESP32 libraries, only if arduino-cli is installed.
where arduino-cli >nul 2>nul && (
  echo Installing ESP32 sketch libraries with arduino-cli ...
  arduino-cli lib install "Adafruit Fingerprint Sensor Library" "HX711" "ESP32Servo" "ArduinoJson"
)

echo [3/3] Done. Starting E-Ration ...
echo.
echo   Website:      http://localhost:8000
echo   Logins:       admin / admin   ^|   demo customers password: user@123
echo   ESP32 node:   set API_URL in hardware\ration_node\ration_node.ino to http://^<this PC IP^>:8000/api/node.php
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do echo                 this PC IP:%%a
echo   Next time just run start-demo.bat
echo.
call "%~dp0start-demo.bat"
exit /b 0

:fail
echo.
echo Setup failed. Check your internet connection and run install.bat again.
pause
exit /b 1
