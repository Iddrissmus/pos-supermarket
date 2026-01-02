#!/bin/bash

# Test the business registration endpoint with curl
# This simulates the form submission to check server-side processing

echo "=== Testing Business Registration Flow ==="
echo ""
echo "1. Clearing logs..."
php artisan log:clear

echo ""
echo "2. Submitting test registration..."

curl -X POST http://localhost:8000/business-signup \
  -H "Content-Type: multipart/form-data" \
  -F "business_name=Test Business curl" \
  -F "owner_name=Test Owner" \
  -F "owner_email=testcurl$(date +%s)@example.com" \
  -F "owner_phone=0240000000" \
  -F "branch_name=Main Branch" \
  -F "address=123 Test Street" \
  -F "region=Greater Accra" \
  -F "latitude=5.6" \
  -F "longitude=-0.2" \
  -F "plan_type=starter" \
  -F "business_type_id=1" \
  -v 2>&1 | grep -E "(Location:|< HTTP|Failed)"

echo ""
echo "3. Checking logs for errors..."
echo ""
tail -50 storage/logs/laravel.log | grep -A5 -B2 "guest business signup"

echo ""
echo "=== Test Complete ==="
