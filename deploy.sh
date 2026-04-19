#!/bin/bash

# ============================================
# Air Bersih - Local to Production Deploy Script
# Usage: ./deploy.sh
# ============================================

echo "🚀 Air Bersih Deployment Script"
echo "================================"

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Clear caches
echo -e "${YELLOW}[1/8] Clearing caches...${NC}"
php artisan config:clear cache:clear view:clear route:cache
echo -e "${GREEN}✓ Caches cleared${NC}"

# 2. Check .env
echo -e "${YELLOW}[2/8] Checking .env file...${NC}"
if [ ! -f .env ]; then
    echo -e "${RED}✗ .env file not found!${NC}"
    echo "Run: cp .env.example .env"
    exit 1
fi
echo -e "${GREEN}✓ .env exists${NC}"

# 3. Generate key if needed
echo -e "${YELLOW}[3/8] Checking APP_KEY...${NC}"
if grep -q "APP_KEY=base64:" .env; then
    echo -e "${GREEN}✓ APP_KEY already set${NC}"
else
    echo "Generating APP_KEY..."
    php artisan key:generate
    echo -e "${GREEN}✓ APP_KEY generated${NC}"
fi

# 4. Install composer dependencies
echo -e "${YELLOW}[4/8] Installing composer dependencies...${NC}"
if [ ! -d "vendor" ]; then
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
else
    echo -e "${GREEN}✓ Vendor folder already exists${NC}"
fi

# 5. Build assets
echo -e "${YELLOW}[5/8] Building assets...${NC}"
if [ -f "package.json" ]; then
    npm install
    npm run build
    echo -e "${GREEN}✓ Assets built${NC}"
else
    echo -e "${YELLOW}⊘ No package.json found, skipping npm${NC}"
fi

# 6. Optimize for production
echo -e "${YELLOW}[6/8] Optimizing for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
echo -e "${GREEN}✓ Production optimization complete${NC}"

# 7. Set permissions
echo -e "${YELLOW}[7/8] Setting folder permissions...${NC}"
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/uploads 2>/dev/null || mkdir -p public/uploads && chmod -R 755 public/uploads
echo -e "${GREEN}✓ Permissions set${NC}"

# 8. Database
echo -e "${YELLOW}[8/8] Database operations...${NC}"
echo "Run these commands when deployed:"
echo "  php artisan migrate --force"
echo "  php artisan db:seed"

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✓ Deployment preparation complete!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo "Next steps:"
echo "1. Review .env configuration"
echo "2. Upload project to shared hosting"
echo "3. Run database migrations: php artisan migrate"
echo "4. Seed database: php artisan db:seed"
echo "5. Test application in browser"
