#!/bin/bash

# Compile assets with Vazir font integration
echo "🎨 Compiling assets with Vazir font..."

# Download Vazir font if not already present
if [ ! -d "public/fonts/vazir" ]; then
    echo "📥 Downloading Vazir font..."
    ./download-vazir-font.sh
fi

# Install npm dependencies if needed
if [ ! -d "node_modules" ]; then
    echo "📦 Installing npm dependencies..."
    npm install
fi

# Compile assets
echo "🔨 Compiling SCSS with Vazir font..."
npm run dev

echo "✅ Assets compiled successfully with Vazir font integration!"
echo ""
echo "📋 Next steps:"
echo "1. The Vazir font is now integrated into your CSS"
echo "2. Use Persian text components: <x-persian-text>متن فارسی</x-persian-text>"
echo "3. Add 'persian-text' class to elements for Persian styling"
echo "4. Use 'mixed-content' class for mixed Persian/English content"