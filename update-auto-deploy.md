# Auto-Deploy Script Update

## Issue Fixed

The auto-deploy.sh script has been updated to fix an issue with environment variable formatting in the .env file. The previous version of the script was attempting to replace quotes with the same quotes, which didn't resolve formatting problems with special Unicode quotes.

## Changes Made

1. Modified the sed commands in the auto-deploy.sh script to properly handle quotes in environment variables:
   ```bash
   # Old approach (problematic)
   sed -i 's/APP_NAME="Negarin Crafts"/APP_NAME="Negarin Crafts"/' .env
   
   # New approach (fixed)
   sed -i 's/APP_NAME=.*/APP_NAME="Negarin Crafts"/' .env
   ```

2. Created a test script (`test-env-format.sh`) to verify that environment variables are properly formatted.

## How to Use

1. Make sure both scripts are executable:
   ```bash
   chmod +x auto-deploy.sh
   chmod +x test-env-format.sh
   ```

2. Run the auto-deploy.sh script as usual:
   ```bash
   ./auto-deploy.sh
   ```

3. If you encounter any issues with environment variable formatting, you can run the test script to verify the format:
   ```bash
   ./test-env-format.sh
   ```

## Why This Fix Works

The previous approach tried to replace exact patterns with the same text, which doesn't fix malformed quotes. The new approach:

1. Matches the variable name regardless of what follows it
2. Replaces the entire line with a properly formatted version
3. Ensures consistent quote usage throughout the file

This prevents the "unexpected character" error that was occurring during deployment.