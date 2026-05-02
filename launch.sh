#!/bin/bash

# Configuration
# Get the directory where the script is located
PROJECT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
APP_URL="http://localhost:8000"
LOG_DIR="$PROJECT_DIR/storage/logs/launcher"

# Create log directory if it doesn't exist
mkdir -p "$LOG_DIR"

echo "Starting TradeJournal Launcher..."

# Navigate to project directory
cd "$PROJECT_DIR" || exit

# 1. Start PHP Artisan Serve if not already running
if ! lsof -i :8000 > /dev/null; then
    echo "Starting Laravel server..."
    nohup php artisan serve --port=8000 > "$LOG_DIR/laravel.log" 2>&1 &
    # Wait for server to be ready
    sleep 2
else
    echo "Laravel server is already running."
fi

# 2. Start Vite (npm run dev) if not already running
# We check for port 5173 which is default for Vite
if ! lsof -i :5173 > /dev/null; then
    echo "Starting Vite dev server..."
    nohup npm run dev > "$LOG_DIR/vite.log" 2>&1 &
    # Wait for Vite to be ready
    sleep 2
else
    echo "Vite dev server is already running."
fi

# 3. Open in browser (app mode if possible)
echo "Opening TradeJournal..."
if command -v google-chrome > /dev/null; then
    google-chrome --app="$APP_URL" &
elif command -v chromium-browser > /dev/null; then
    chromium-browser --app="$APP_URL" &
elif command -v brave-browser > /dev/null; then
    brave-browser --app="$APP_URL" &
else
    xdg-open "$APP_URL"
fi

echo "Done!"
