#!/bin/bash

# Endpoint Verification Script
# Tests if authentication endpoints work correctly

echo "🔍 POS Supermarket - Endpoint Verification"
echo "==========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test URL
BASE_URL="http://localhost:8000"

# Test 1: Check if app is running
echo "Test 1: Checking if application is running..."
if curl -s -o /dev/null -w "%{http_code}" "$BASE_URL" | grep -q "200\|302"; then
    echo -e "${GREEN}✓ Application is running${NC}"
else
    echo -e "${RED}✗ Application is NOT running on $BASE_URL${NC}"
    echo -e "${YELLOW}Start with: php artisan serve${NC}"
    exit 1
fi
echo ""

# Test 2: Check cashier login page
echo "Test 2: Checking cashier login page..."
RESPONSE_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/login/cashier")
if [ "$RESPONSE_CODE" = "200" ]; then
    echo -e "${GREEN}✓ GET /login/cashier returns 200${NC}"
else
    echo -e "${RED}✗ GET /login/cashier returns $RESPONSE_CODE${NC}"
    if [ "$RESPONSE_CODE" = "404" ]; then
        echo -e "${YELLOW}This is why JMeter is getting 404 errors!${NC}"
    fi
fi
echo ""

# Test 3: Check if CSRF token exists in page
echo "Test 3: Checking if CSRF token exists..."
CSRF_CHECK=$(curl -s "$BASE_URL/login/cashier" | grep -c "_token")
if [ "$CSRF_CHECK" -gt 0 ]; then
    echo -e "${GREEN}✓ CSRF token found in login page${NC}"
    TOKEN=$(curl -s "$BASE_URL/login/cashier" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
    echo -e "  Token preview: ${TOKEN:0:20}..."
else
    echo -e "${RED}✗ CSRF token NOT found in login page${NC}"
    echo -e "${YELLOW}This will cause 419 errors!${NC}"
fi
echo ""

# Test 4: Test full login flow
echo "Test 4: Testing full login flow..."
echo "  Creating temporary files..."
curl -c /tmp/pos_cookies.txt -s "$BASE_URL/login/cashier" > /tmp/pos_login_page.html

TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/pos_login_page.html | head -1)

if [ -z "$TOKEN" ]; then
    echo -e "${RED}✗ Could not extract CSRF token${NC}"
else
    echo -e "${GREEN}✓ Token extracted: ${TOKEN:0:30}...${NC}"
    
    echo "  Attempting login..."
    LOGIN_RESPONSE=$(curl -b /tmp/pos_cookies.txt \
        -X POST "$BASE_URL/login/cashier" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -H "X-Requested-With: XMLHttpRequest" \
        -d "email=cashier@pos.com&password=password&_token=$TOKEN&remember=0" \
        -L -s -o /tmp/pos_login_result.txt -w "%{http_code}")
    
    if [ "$LOGIN_RESPONSE" = "200" ]; then
        echo -e "${GREEN}✓ Login successful (200)${NC}"
    elif [ "$LOGIN_RESPONSE" = "302" ]; then
        echo -e "${GREEN}✓ Login successful (302 redirect)${NC}"
    elif [ "$LOGIN_RESPONSE" = "419" ]; then
        echo -e "${RED}✗ Login failed with 419 (CSRF token mismatch)${NC}"
        echo -e "${YELLOW}This is the error JMeter is getting!${NC}"
    elif [ "$LOGIN_RESPONSE" = "405" ]; then
        echo -e "${RED}✗ Login failed with 405 (Method Not Allowed)${NC}"
        echo -e "${YELLOW}Checking response...${NC}"
        cat /tmp/pos_login_result.txt | head -20
    else
        echo -e "${RED}✗ Login failed with status $LOGIN_RESPONSE${NC}"
        echo "Response preview:"
        cat /tmp/pos_login_result.txt | head -20
    fi
fi
echo ""

# Cleanup
rm -f /tmp/pos_cookies.txt /tmp/pos_login_page.html /tmp/pos_login_result.txt

# Summary
echo "==========================================="
echo "Summary:"
echo "- If all tests pass, JMeter should work after fixes"
echo "- If tests fail, fix the application first"
echo ""
echo "JMeter test plan has been updated with:"
echo "  ✓ Fixed CSRF token reference name"
echo "  ✓ Improved regex pattern"
echo "  ✓ Added debug sampler"
echo "  ✓ Enhanced logging for troubleshooting"
echo ""
echo "Run JMeter test again:"
echo "  cd jmeter-tests"
echo "  jmeter -n -t POS_Supermarket.jmx -l results/test_fixed.jtl -e -o results/test_fixed_report"
