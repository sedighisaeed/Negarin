#!/bin/bash

# Test script to verify server access via both domain and IP

echo "Testing access to Negarin server..."

# Test domain access
echo "\nTesting domain access (negarincrafts.com):"
curl -I https://negarincrafts.com

# Test IP access
echo "\nTesting IP access (193.36.85.235):"
curl -I https://193.36.85.235

echo "\nTests completed. If both tests returned HTTP 200 responses, your configuration is working correctly."
echo "If you see certificate errors on the IP test, this is normal as certificates are typically issued for domains only."
echo "The important part is that the server responds on both the domain and IP address."