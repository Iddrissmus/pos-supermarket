#!/bin/bash

# JMeter Test Monitoring Script
# This script monitors server resources during JMeter tests

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}==================================${NC}"
echo -e "${BLUE}  POS Supermarket - Test Monitor  ${NC}"
echo -e "${BLUE}==================================${NC}\n"

# Configuration
LOG_DIR="jmeter-tests/monitoring"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
CPU_LOG="$LOG_DIR/cpu_usage_$TIMESTAMP.log"
MEM_LOG="$LOG_DIR/memory_usage_$TIMESTAMP.log"
DISK_LOG="$LOG_DIR/disk_io_$TIMESTAMP.log"
NETWORK_LOG="$LOG_DIR/network_$TIMESTAMP.log"
MYSQL_LOG="$LOG_DIR/mysql_stats_$TIMESTAMP.log"
LARAVEL_LOG="storage/logs/laravel.log"

# Create monitoring directory
mkdir -p "$LOG_DIR"

# Check if application is running
check_app_status() {
    echo -e "${YELLOW}Checking application status...${NC}"
    
    if curl -s http://localhost:8000 > /dev/null; then
        echo -e "${GREEN}✓ Application is running on http://localhost:8000${NC}"
    else
        echo -e "${RED}✗ Application is NOT running!${NC}"
        echo -e "${YELLOW}Start the application with: php artisan serve${NC}"
        exit 1
    fi
    
    # Check database connection
    if php artisan db:show > /dev/null 2>&1; then
        echo -e "${GREEN}✓ Database connection OK${NC}"
    else
        echo -e "${RED}✗ Database connection FAILED!${NC}"
        exit 1
    fi
    
    echo ""
}

# Monitor CPU usage
monitor_cpu() {
    echo -e "${YELLOW}Monitoring CPU usage...${NC}"
    echo "Timestamp,CPU%" > "$CPU_LOG"
    
    while true; do
        timestamp=$(date +%Y-%m-%d\ %H:%M:%S)
        cpu_usage=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')
        echo "$timestamp,$cpu_usage" >> "$CPU_LOG"
        sleep 5
    done &
    
    CPU_PID=$!
    echo -e "${GREEN}✓ CPU monitoring started (PID: $CPU_PID)${NC}"
}

# Monitor Memory usage
monitor_memory() {
    echo -e "${YELLOW}Monitoring Memory usage...${NC}"
    echo "Timestamp,Used_MB,Free_MB,Usage%" > "$MEM_LOG"
    
    while true; do
        timestamp=$(date +%Y-%m-%d\ %H:%M:%S)
        mem_info=$(free -m | awk 'NR==2{printf "%s,%s,%.2f", $3,$4,$3*100/$2}')
        echo "$timestamp,$mem_info" >> "$MEM_LOG"
        sleep 5
    done &
    
    MEM_PID=$!
    echo -e "${GREEN}✓ Memory monitoring started (PID: $MEM_PID)${NC}"
}

# Monitor MySQL
monitor_mysql() {
    echo -e "${YELLOW}Monitoring MySQL...${NC}"
    echo "Timestamp,Threads_connected,Questions,Slow_queries" > "$MYSQL_LOG"
    
    # Get MySQL credentials from .env
    DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
    DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)
    DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
    DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)
    
    while true; do
        timestamp=$(date +%Y-%m-%d\ %H:%M:%S)
        mysql_stats=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SHOW GLOBAL STATUS WHERE Variable_name IN ('Threads_connected', 'Questions', 'Slow_queries');" -N | awk '{printf "%s,", $2}' | sed 's/,$//')
        echo "$timestamp,$mysql_stats" >> "$MYSQL_LOG"
        sleep 10
    done &
    
    MYSQL_PID=$!
    echo -e "${GREEN}✓ MySQL monitoring started (PID: $MYSQL_PID)${NC}"
}

# Monitor Laravel logs
monitor_laravel_logs() {
    echo -e "${YELLOW}Monitoring Laravel logs...${NC}"
    
    # Clear existing log
    : > "$LARAVEL_LOG"
    
    # Tail logs in background
    tail -f "$LARAVEL_LOG" | grep --line-buffered -E "(ERROR|CRITICAL|Exception)" &
    
    LOG_PID=$!
    echo -e "${GREEN}✓ Laravel log monitoring started (PID: $LOG_PID)${NC}"
}

# Display real-time stats
display_stats() {
    echo -e "\n${BLUE}Real-time Statistics:${NC}"
    echo -e "${YELLOW}Press Ctrl+C to stop monitoring${NC}\n"
    
    while true; do
        clear
        echo -e "${BLUE}==================================${NC}"
        echo -e "${BLUE}  Real-time Server Statistics     ${NC}"
        echo -e "${BLUE}==================================${NC}\n"
        
        # System stats
        echo -e "${GREEN}System Resources:${NC}"
        echo "  CPU Usage: $(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')%"
        echo "  Memory: $(free -h | awk 'NR==2{printf "Used: %s / Total: %s (%.2f%%)", $3,$2,$3*100/$2}')"
        echo "  Disk I/O: $(iostat -x 1 2 | tail -n 2 | awk 'NR==2{printf "Read: %.2f MB/s, Write: %.2f MB/s", $6/1024, $7/1024}')" 2>/dev/null
        
        echo ""
        
        # Laravel stats
        echo -e "${GREEN}Laravel Application:${NC}"
        echo "  Processes: $(ps aux | grep -c "[p]hp artisan serve")"
        echo "  Log size: $(du -h "$LARAVEL_LOG" 2>/dev/null | cut -f1 || echo "0K")"
        echo "  Recent errors: $(grep -c "ERROR" "$LARAVEL_LOG" 2>/dev/null || echo "0")"
        
        echo ""
        
        # MySQL stats
        echo -e "${GREEN}Database (MySQL):${NC}"
        mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "
            SELECT 
                VARIABLE_VALUE as 'Connections'
            FROM performance_schema.global_status 
            WHERE VARIABLE_NAME = 'Threads_connected';
            
            SELECT 
                VARIABLE_VALUE as 'Queries/sec'
            FROM performance_schema.global_status 
            WHERE VARIABLE_NAME = 'Questions';
        " 2>/dev/null || echo "  Unable to fetch MySQL stats"
        
        echo ""
        echo -e "${YELLOW}Monitoring logs saved to: $LOG_DIR${NC}"
        echo -e "${YELLOW}Update every 5 seconds. Press Ctrl+C to stop.${NC}"
        
        sleep 5
    done
}

# Cleanup function
cleanup() {
    echo -e "\n\n${YELLOW}Stopping monitoring...${NC}"
    
    # Kill monitoring processes
    [ ! -z "$CPU_PID" ] && kill $CPU_PID 2>/dev/null && echo -e "${GREEN}✓ Stopped CPU monitoring${NC}"
    [ ! -z "$MEM_PID" ] && kill $MEM_PID 2>/dev/null && echo -e "${GREEN}✓ Stopped Memory monitoring${NC}"
    [ ! -z "$MYSQL_PID" ] && kill $MYSQL_PID 2>/dev/null && echo -e "${GREEN}✓ Stopped MySQL monitoring${NC}"
    [ ! -z "$LOG_PID" ] && kill $LOG_PID 2>/dev/null && echo -e "${GREEN}✓ Stopped Log monitoring${NC}"
    
    echo -e "\n${BLUE}Monitoring Results:${NC}"
    echo -e "  CPU Log: $CPU_LOG"
    echo -e "  Memory Log: $MEM_LOG"
    echo -e "  MySQL Log: $MYSQL_LOG"
    echo -e "  Laravel Log: $LARAVEL_LOG"
    
    echo -e "\n${GREEN}Generate analysis report with:${NC}"
    echo -e "  python3 jmeter-tests/analyze_monitoring.py $LOG_DIR"
    
    exit 0
}

# Trap Ctrl+C
trap cleanup SIGINT SIGTERM

# Main execution
check_app_status
monitor_cpu
monitor_memory
monitor_mysql
monitor_laravel_logs

echo -e "\n${GREEN}All monitoring services started!${NC}\n"
sleep 2

display_stats
