#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "🔍 Testing .env file format..."

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo -e "${RED}Error: .env file not found!${NC}"
    exit 1
fi

# Test critical variables
echo "Testing APP_NAME format..."
APP_NAME_LINE=$(grep "^APP_NAME=" .env)
if [ -z "$APP_NAME_LINE" ]; then
    echo -e "${YELLOW}Warning: APP_NAME not found in .env file${NC}"
else
    if echo "$APP_NAME_LINE" | grep -q "^APP_NAME=\".*\"$"; then
        echo -e "${GREEN}✅ APP_NAME is properly formatted${NC}"
    else
        echo -e "${RED}❌ APP_NAME has quote issues: $APP_NAME_LINE${NC}"
    fi
fi

echo "Testing APP_DOMAIN format..."
APP_DOMAIN_LINE=$(grep "^APP_DOMAIN=" .env)
if [ -z "$APP_DOMAIN_LINE" ]; then
    echo -e "${YELLOW}Warning: APP_DOMAIN not found in .env file${NC}"
else
    if echo "$APP_DOMAIN_LINE" | grep -q "^APP_DOMAIN=\".*\"$"; then
        echo -e "${GREEN}✅ APP_DOMAIN is properly formatted${NC}"
    else
        echo -e "${RED}❌ APP_DOMAIN has quote issues: $APP_DOMAIN_LINE${NC}"
    fi
fi

echo "Testing DB_PASSWORD format..."
DB_PASSWORD_LINE=$(grep "^DB_PASSWORD=" .env)
if [ -z "$DB_PASSWORD_LINE" ]; then
    echo -e "${YELLOW}Warning: DB_PASSWORD not found in .env file${NC}"
else
    if echo "$DB_PASSWORD_LINE" | grep -q "^DB_PASSWORD=\".*\"$"; then
        echo -e "${GREEN}✅ DB_PASSWORD is properly formatted${NC}"
    else
        echo -e "${RED}❌ DB_PASSWORD has quote issues: $DB_PASSWORD_LINE${NC}"
    fi
fi

echo "Testing INSTANCE_CONTACT_EMAIL format..."
EMAIL_LINE=$(grep "^INSTANCE_CONTACT_EMAIL=" .env)
if [ -z "$EMAIL_LINE" ]; then
    echo -e "${YELLOW}Warning: INSTANCE_CONTACT_EMAIL not found in .env file${NC}"
else
    if echo "$EMAIL_LINE" | grep -q "^INSTANCE_CONTACT_EMAIL=\".*\"$"; then
        echo -e "${GREEN}✅ INSTANCE_CONTACT_EMAIL is properly formatted${NC}"
    else
        echo -e "${RED}❌ INSTANCE_CONTACT_EMAIL has quote issues: $EMAIL_LINE${NC}"
    fi
fi

# Check for any variables with unbalanced quotes
echo -e "\n${YELLOW}Checking for other potential quote issues...${NC}"
grep -n "\"" .env | grep -v "^#" | while read -r line; do
    line_num=$(echo "$line" | cut -d':' -f1)
    content=$(echo "$line" | cut -d':' -f2-)
    
    # Count quotes in the line
    quote_count=$(echo "$content" | grep -o '"' | wc -l)
    
    # Check if quotes are unbalanced (not exactly 2)
    if [ "$quote_count" -ne 2 ]; then
        echo -e "${RED}❌ Line $line_num has unbalanced quotes: $content${NC}"
    fi
done

echo -e "\n${GREEN}✅ Test completed!${NC}"