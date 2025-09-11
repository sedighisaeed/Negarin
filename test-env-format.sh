#!/bin/bash

# Test script to verify environment variable formatting
# This script checks if the .env file has properly formatted variables

set -e  # Exit on any error

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

echo "🔍 Testing .env file format..."

# Check if .env file exists
if [ ! -f ".env" ]; then
    print_error "No .env file found. Please run auto-deploy.sh first."
    exit 1
fi

# Test APP_NAME format
APP_NAME_LINE=$(grep "^APP_NAME=" .env)
if [ $? -ne 0 ]; then
    print_error "APP_NAME not found in .env file"
    exit 1
fi

# Check if APP_NAME has proper quotes
if [[ $APP_NAME_LINE =~ ^APP_NAME="[^"]*"$ ]]; then
    print_success "APP_NAME format is correct"
else
    print_error "APP_NAME format is incorrect: $APP_NAME_LINE"
    print_warning "Should be in format: APP_NAME=\"Negarin Crafts\""
    exit 1
fi

# Test APP_DOMAIN format
APP_DOMAIN_LINE=$(grep "^APP_DOMAIN=" .env)
if [ $? -ne 0 ]; then
    print_error "APP_DOMAIN not found in .env file"
    exit 1
fi

# Check if APP_DOMAIN has proper quotes
if [[ $APP_DOMAIN_LINE =~ ^APP_DOMAIN="[^"]*"$ ]]; then
    print_success "APP_DOMAIN format is correct"
else
    print_error "APP_DOMAIN format is incorrect: $APP_DOMAIN_LINE"
    print_warning "Should be in format: APP_DOMAIN=\"negarincrafts.com\""
    exit 1
fi

# Check for any smart quotes in the file
if grep -q "[