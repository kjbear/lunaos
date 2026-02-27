#!/bin/bash

# LunaOS Mission Control Fix Script
# Run this to fix the rendering issues

echo "🌙 Fixing Mission Control rendering..."

cd /Users/kobear/.openclaw/workspace/lunaos

# 1. Clear Laravel view cache
echo "📦 Clearing view cache..."
php artisan view:clear

# 2. Clear config cache
echo "🔧 Clearing config cache..."
php artisan config:clear

# 3. Clear application cache
echo "🧹 Clearing app cache..."
php artisan cache:clear

# 4. Rebuild Vite assets
echo "⚡ Rebuilding Vite assets..."
npm run build

# 5. Check permissions
echo "🔐 Fixing permissions..."
chmod -R 755 storage bootstrap/cache

echo "✅ Done! Refresh your browser (Cmd+Shift+R)"
echo ""
echo "Then visit: http://lunaos.test/mission-control-polished"
