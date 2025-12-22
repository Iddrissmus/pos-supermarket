#!/bin/bash

# JMeter Test Execution Script
# Runs progressive load tests automatically

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}======================================${NC}"
echo -e "${BLUE}  POS Supermarket - Test Execution   ${NC}"
echo -e "${BLUE}======================================${NC}\n"

# Configuration
RESULTS_DIR="jmeter-tests/results"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create results directory
mkdir -p "$RESULTS_DIR"

# Check prerequisites
check_prerequisites() {
    echo -e "${YELLOW}Checking prerequisites...${NC}"
    
    # Check JMeter
    if ! command -v jmeter &> /dev/null; then
        echo -e "${RED}✗ JMeter not found!${NC}"
        echo -e "${YELLOW}Install with: brew install jmeter (macOS) or apt install jmeter (Ubuntu)${NC}"
        exit 1
    fi
    echo -e "${GREEN}✓ JMeter installed: $(jmeter --version 2>&1 | head -n1)${NC}"
    
    # Check Java
    if ! command -v java &> /dev/null; then
        echo -e "${RED}✗ Java not found!${NC}"
        exit 1
    fi
    echo -e "${GREEN}✓ Java installed: $(java -version 2>&1 | head -n1)${NC}"
    
    # Check application
    if ! curl -s http://localhost:8000 > /dev/null; then
        echo -e "${RED}✗ Application not running on http://localhost:8000${NC}"
        echo -e "${YELLOW}Start with: php artisan serve${NC}"
        exit 1
    fi
    echo -e "${GREEN}✓ Application is running${NC}"
    
    # Check test files
    if [ ! -f "jmeter-tests/users.csv" ]; then
        echo -e "${RED}✗ Test data files not found!${NC}"
        exit 1
    fi
    echo -e "${GREEN}✓ Test data files present${NC}\n"
}

# Display test menu
show_menu() {
    echo -e "${BLUE}Select test to run:${NC}"
    echo "  1) Smoke Test (1 user, 1 minute)"
    echo "  2) Baseline Test (5 users, 5 minutes)"
    echo "  3) Light Load Test (25 users, 10 minutes)"
    echo "  4) Normal Load Test (50 users, 10 minutes)"
    echo "  5) Peak Load Test (100 users, 15 minutes)"
    echo "  6) Stress Test (200 users, 20 minutes)"
    echo "  7) Extreme Stress Test (500 users, 15 minutes)"
    echo "  8) Endurance Test (50 users, 2 hours)"
    echo "  9) Custom Test"
    echo "  0) Exit"
    echo ""
    read -p "Enter choice [0-9]: " choice
}

# Run test
run_test() {
    local test_name=$1
    local users=$2
    local duration=$3
    local rampup=$4
    
    echo -e "\n${BLUE}==================================${NC}"
    echo -e "${BLUE}  Running: $test_name${NC}"
    echo -e "${BLUE}==================================${NC}"
    echo -e "  Users: ${GREEN}$users${NC}"
    echo -e "  Duration: ${GREEN}$duration seconds${NC}"
    echo -e "  Ramp-up: ${GREEN}$rampup seconds${NC}\n"
    
    # Create test-specific directory
    local test_dir="$RESULTS_DIR/${test_name// /_}_${TIMESTAMP}"
    mkdir -p "$test_dir"
    
    # Log file
    local log_file="$test_dir/jmeter.log"
    
    # Start monitoring in background
    echo -e "${YELLOW}Starting system monitoring...${NC}"
    ./jmeter-tests/monitor.sh > "$test_dir/monitor.log" 2>&1 &
    MONITOR_PID=$!
    
    sleep 3
    
    # Note: You need to create the actual .jmx test plan file
    # This is a template - you'll create the actual test plan in JMeter GUI
    echo -e "${YELLOW}Note: You need to create the .jmx test plan first!${NC}"
    echo -e "${YELLOW}Follow the README.md instructions to create your test plan.${NC}"
    echo -e "${YELLOW}Once created, uncomment the jmeter command below.${NC}\n"
    
    # Run JMeter test (uncomment when you have the .jmx file)
    # jmeter -n \
    #   -t jmeter-tests/POS_LoadTest.jmx \
    #   -Jusers=$users \
    #   -Jduration=$duration \
    #   -Jrampup=$rampup \
    #   -l "$test_dir/results.jtl" \
    #   -j "$log_file" \
    #   -e -o "$test_dir/html-report"
    
    # For now, simulate test
    echo -e "${YELLOW}[SIMULATION MODE]${NC}"
    echo "Test would run with these parameters..."
    sleep 5
    
    # Stop monitoring
    kill $MONITOR_PID 2>/dev/null
    
    echo -e "\n${GREEN}✓ Test completed!${NC}"
    echo -e "  Results: $test_dir"
    echo -e "  HTML Report: $test_dir/html-report/index.html"
    echo -e "  Monitor Log: $test_dir/monitor.log\n"
    
    # Generate summary
    generate_summary "$test_dir" "$test_name" "$users" "$duration"
}

# Generate test summary
generate_summary() {
    local test_dir=$1
    local test_name=$2
    local users=$3
    local duration=$4
    
    local summary_file="$test_dir/TEST_SUMMARY.md"
    
    cat > "$summary_file" << EOF
# Test Summary: $test_name

**Date:** $(date)  
**Test Configuration:**
- Users: $users
- Duration: $duration seconds
- Ramp-up: Proportional

## Results

### Response Times
- Average: N/A (test not run yet)
- Median: N/A
- 90th Percentile: N/A
- 95th Percentile: N/A
- 99th Percentile: N/A
- Min: N/A
- Max: N/A

### Throughput
- Requests/second: N/A
- Transactions/second: N/A

### Error Rate
- Total Requests: N/A
- Failed Requests: N/A
- Error Rate: N/A%

## System Resources

### CPU
- Average: N/A%
- Peak: N/A%

### Memory
- Average: N/A MB
- Peak: N/A MB

### Database
- Connections (avg): N/A
- Queries/sec: N/A
- Slow queries: N/A

## Issues Found
- None reported (test not run)

## Recommendations
1. Create the actual JMeter test plan (.jmx file)
2. Run the test
3. Analyze results
4. Optimize application based on findings

---
*Results Directory:* $test_dir
EOF

    echo -e "${GREEN}✓ Summary generated: $summary_file${NC}"
}

# Main execution
check_prerequisites

while true; do
    show_menu
    
    case $choice in
        1)
            run_test "Smoke Test" 1 60 10
            ;;
        2)
            run_test "Baseline Test" 5 300 30
            ;;
        3)
            run_test "Light Load Test" 25 600 120
            ;;
        4)
            run_test "Normal Load Test" 50 600 300
            ;;
        5)
            run_test "Peak Load Test" 100 900 300
            ;;
        6)
            run_test "Stress Test" 200 1200 300
            ;;
        7)
            run_test "Extreme Stress Test" 500 900 120
            ;;
        8)
            run_test "Endurance Test" 50 7200 600
            ;;
        9)
            echo ""
            read -p "Enter number of users: " custom_users
            read -p "Enter duration (seconds): " custom_duration
            read -p "Enter ramp-up (seconds): " custom_rampup
            run_test "Custom Test" $custom_users $custom_duration $custom_rampup
            ;;
        0)
            echo -e "\n${GREEN}Goodbye!${NC}\n"
            exit 0
            ;;
        *)
            echo -e "${RED}Invalid choice!${NC}\n"
            ;;
    esac
    
    read -p "Press Enter to continue..."
done
