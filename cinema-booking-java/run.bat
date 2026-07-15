@echo off
echo ============================================
echo   Cinema Ticket System - JavaFX Launcher
echo ============================================
echo.

REM ── EDIT THIS PATH to match your JavaFX SDK location ──────────────────────
set FX_PATH=C:\javafx-sdk-21\lib
REM ──────────────────────────────────────────────────────────────────────────

if not exist "%FX_PATH%" (
    echo ERROR: JavaFX SDK not found at: %FX_PATH%
    echo Please download from https://openjfx.io/ and update FX_PATH in this script.
    pause
    exit /b 1
)

echo [1/2] Compiling...
if not exist bin mkdir bin
javac --module-path "%FX_PATH%" --add-modules javafx.controls,javafx.fxml -d bin src\*.java
if errorlevel 1 (
    echo COMPILATION FAILED. See errors above.
    pause
    exit /b 1
)

echo [2/2] Launching Cinema Ticket System...
echo.
java --module-path "%FX_PATH%" --add-modules javafx.controls,javafx.fxml -cp bin Main
pause
