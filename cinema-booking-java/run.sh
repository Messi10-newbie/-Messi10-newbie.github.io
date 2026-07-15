#!/bin/bash
echo "============================================"
echo "  Cinema Ticket System - JavaFX Launcher"
echo "============================================"

# ── EDIT THIS PATH to match your JavaFX SDK location ──────────────────────
FX_PATH="/opt/javafx-sdk-21/lib"
# ──────────────────────────────────────────────────────────────────────────

if [ ! -d "$FX_PATH" ]; then
    echo "ERROR: JavaFX SDK not found at: $FX_PATH"
    echo "Please download from https://openjfx.io/ and update FX_PATH in this script."
    exit 1
fi

mkdir -p bin
echo "[1/2] Compiling..."
javac --module-path "$FX_PATH" --add-modules javafx.controls,javafx.fxml -d bin src/*.java
if [ $? -ne 0 ]; then
    echo "COMPILATION FAILED."
    exit 1
fi

echo "[2/2] Launching..."
java --module-path "$FX_PATH" --add-modules javafx.controls,javafx.fxml -cp bin Main
