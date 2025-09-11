#!/bin/bash

# Download Vazir Font for offline use in Negarin
echo "📥 Downloading Vazir Font for offline use..."

# Create fonts directory
mkdir -p public/fonts/vazir
mkdir -p resources/assets/fonts/vazir

# Download Vazir font files from GitHub releases
VAZIR_VERSION="v30.1.0"
BASE_URL="https://github.com/rastikerdar/vazir-font/releases/download/${VAZIR_VERSION}"

echo "Downloading Vazir font files..."

# Download different weights and formats
curl -L "${BASE_URL}/vazir-font-${VAZIR_VERSION}.zip" -o vazir-font.zip

# Extract the font files
unzip -q vazir-font.zip

# Copy font files to public directory
cp vazir-font-*/dist/Vazir*.woff2 public/fonts/vazir/ 2>/dev/null || true
cp vazir-font-*/dist/Vazir*.woff public/fonts/vazir/ 2>/dev/null || true
cp vazir-font-*/dist/Vazir*.ttf public/fonts/vazir/ 2>/dev/null || true

# Copy to resources directory as well
cp vazir-font-*/dist/Vazir*.woff2 resources/assets/fonts/vazir/ 2>/dev/null || true
cp vazir-font-*/dist/Vazir*.woff resources/assets/fonts/vazir/ 2>/dev/null || true
cp vazir-font-*/dist/Vazir*.ttf resources/assets/fonts/vazir/ 2>/dev/null || true

# Clean up
rm -rf vazir-font.zip vazir-font-*

echo "✅ Vazir font downloaded and installed"
echo "📁 Font files available in:"
echo "   - public/fonts/vazir/"
echo "   - resources/assets/fonts/vazir/"

# List downloaded files
echo ""
echo "Downloaded font files:"
ls -la public/fonts/vazir/