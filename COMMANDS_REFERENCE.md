# Quick Command Reference

A comprehensive list of all commands you'll use for managing and testing this POS Supermarket application.

---

## Table of Contents

- [Server Management](#server-management)
- [Laravel/PHP Commands](#laravelphp-commands)
- [JMeter Testing](#jmeter-testing)
- [Database Commands](#database-commands)
- [Performance Monitoring](#performance-monitoring)
- [Git Commands](#git-commands)
- [Troubleshooting](#troubleshooting)

---

## Server Management

### Check Service Status

```bash
# Check all services
sudo systemctl status nginx php8.4-fpm redis-server mysql

# Check individual service
sudo systemctl status nginx
```

### Start/Stop/Restart Services

```bash
# Start services
sudo systemctl start nginx
sudo systemctl start php8.4-fpm
sudo systemctl start redis-server

# Stop services
sudo systemctl stop nginx
sudo systemctl stop php8.4-fpm

# Restart services (apply config changes)
sudo systemctl restart nginx php8.4-fpm
```

### Enable/Disable Auto-Start on Boot

```bash
# Enable auto-start
sudo systemctl enable nginx php8.4-fpm redis-server

# Disable auto-start
sudo systemctl disable nginx php8.4-fpm redis-server
```

### Check Server Logs

```bash
# Nginx error log
sudo tail -f /var/log/nginx/error.log

# Nginx access log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM log
sudo tail -f /var/log/php8.4-fpm.log

# Laravel application log
tail -f storage/logs/laravel.log
```

### Nginx Configuration

```bash
# Test Nginx configuration
sudo nginx -t

# Reload Nginx (without stopping)
sudo systemctl reload nginx

# Edit Nginx site configuration
sudo nano /etc/nginx/sites-available/pos-supermarket
```

---

## Laravel/PHP Commands

### Application Startup

```bash
# Development server (single user)
php artisan serve
php artisan serve --port=8001  # Use different port

# Production (Nginx + PHP-FPM handles this automatically)
# Just access http://localhost:8000
```

### Cache Management

```bash
# Clear all caches at once
php artisan optimize:clear

# Clear individual caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Build caches (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Database Operations

```bash
# Run migrations
php artisan migrate

# Run migrations with fresh database
php artisan migrate:fresh

# Run seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=ComprehensiveCategoriesSeeder

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

### Generate Application Key

```bash
php artisan key:generate
```

### View Routes

```bash
# List all routes
php artisan route:list

# Filter routes by name
php artisan route:list --name=sales

# Filter routes by method
php artisan route:list --method=POST
```

### Queue Workers

```bash
# Start queue worker
php artisan queue:work

# Start with specific connection
php artisan queue:work redis

# Run one job and stop
php artisan queue:work --once
```

### Custom Artisan Commands

```bash
# Run stock reorder check
php artisan stock:check-reorder
```

---

## JMeter Testing

### Run Tests from Command Line

```bash
# Navigate to test directory
cd jmeter-tests

# Run test in non-GUI mode with results
jmeter -n -t POS_Supermarket.jmx -l results/test.jtl

# Run test with HTML report generation
jmeter -n -t POS_Supermarket.jmx -l results/test.jtl -e -o results/report

# Run test with specific number of threads (users)
# Edit the .jmx file or use GUI mode first

# Run test and see summary in console
jmeter -n -t POS_Supermarket.jmx -l results/test.jtl 2>&1 | grep summary
```

### View Test Results

```bash
# View last 10 lines of results
tail -10 jmeter-tests/results/test.jtl

# View specific error responses
grep "404\|500\|419" jmeter-tests/results/test.jtl

# Count total requests
wc -l jmeter-tests/results/test.jtl
```

### Open JMeter GUI

```bash
# Open JMeter GUI
jmeter

# Open specific test plan
jmeter -t jmeter-tests/POS_Supermarket.jmx

# Open with specific Java options
JVM_ARGS="-Xms512m -Xmx2048m" jmeter
```

### Common Test Scenarios

```bash
# Baseline test (5 users)
# Set ThreadGroup.num_threads=5 in GUI, then:
jmeter -n -t POS_Supermarket.jmx -l results/baseline_5users.jtl

# Load test (50 users)
# Set ThreadGroup.num_threads=50 in GUI, then:
jmeter -n -t POS_Supermarket.jmx -l results/load_50users.jtl

# Stress test (100+ users)
# Set ThreadGroup.num_threads=102 in GUI, then:
jmeter -n -t POS_Supermarket.jmx -l results/stress_102users.jtl -e -o results/stress_report
```

---

## Database Commands

### MySQL Direct Access

```bash
# Connect to MySQL
mysql -u root -p

# Connect to specific database
mysql -u pos_user -p pos_supermarket

# Run SQL from command line
mysql -u root -p -e "SHOW DATABASES;"

# Import SQL file
mysql -u root -p pos_supermarket < backup.sql

# Export database
mysqldump -u root -p pos_supermarket > backup.sql
```

### Redis Commands

```bash
# Connect to Redis CLI
redis-cli

# Inside redis-cli:
# Ping server
PING

# View all keys
KEYS *

# Get key info
GET key_name

# View database size
DBSIZE

# Flush all data (careful!)
FLUSHALL

# Exit
exit
```

---

## Performance Monitoring

### Run Performance Monitor

```bash
# Run the monitoring script
bash scripts/monitor_performance.sh

# Monitor specific duration (runs until Ctrl+C)
bash scripts/monitor_performance.sh
```

### System Resource Commands

```bash
# Check CPU and memory usage
top
htop  # Better interface (install: sudo apt install htop)

# Check disk usage
df -h

# Check memory usage
free -h

# Check PHP-FPM processes
ps aux | grep php-fpm

# Count PHP-FPM workers
ps aux | grep php-fpm | grep -v grep | wc -l

# Check Nginx processes
ps aux | grep nginx

# Check active connections
sudo netstat -tulpn | grep :8000
ss -tulpn | grep :8000

# Check MySQL connections
mysql -u root -p -e "SHOW PROCESSLIST;"
```

### Laravel Performance

```bash
# Check Laravel queue status
php artisan queue:work --once

# Monitor Laravel logs in real-time
tail -f storage/logs/laravel.log

# Check scheduled tasks
php artisan schedule:list

# Run scheduler (for testing)
php artisan schedule:run
```

---

## Git Commands

### Basic Git Operations

```bash
# Check status
git status

# View changes
git diff

# Stage files
git add .
git add filename.php

# Commit changes
git commit -m "Your commit message"

# Push to remote
git push origin main

# Pull latest changes
git pull origin main

# Create new branch
git checkout -b feature-name

# Switch branches
git checkout main

# View commit history
git log --oneline
```

### View Changed Files

```bash
# Show files changed in last commit
git show --name-only

# Show changes in specific file
git diff filename.php

# Show uncommitted changes
git diff HEAD
```

---

## Troubleshooting

### Permission Issues

```bash
# Fix Laravel storage permissions
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Fix project permissions
sudo chown -R $USER:www-data .
sudo chmod -R 755 .

# Fix parent directory permissions (for Nginx)
chmod +x ~
chmod +x ~/Projects
```

### Clear Everything and Start Fresh

```bash
# Stop all services
sudo systemctl stop nginx php8.4-fpm

# Clear Laravel caches
php artisan optimize:clear

# Clear Redis
redis-cli FLUSHALL

# Restart services
sudo systemctl restart php8.4-fpm nginx redis-server

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Check Configuration

```bash
# Check PHP version
php -v

# Check PHP modules
php -m

# Check Composer version
composer --version

# Check Node/npm version
node -v
npm -v

# Check MySQL version
mysql --version

# Check Redis version
redis-server --version
```

### Find and Kill Process on Port

```bash
# Find process using port 8000
sudo lsof -i :8000
sudo netstat -tulpn | grep :8000

# Kill process by PID
kill -9 PID_NUMBER

# Or kill all PHP processes (careful!)
pkill -9 php
```

### View Error Logs

```bash
# Laravel errors
tail -100 storage/logs/laravel.log

# Nginx errors
sudo tail -100 /var/log/nginx/error.log

# PHP-FPM errors
sudo tail -100 /var/log/php8.4-fpm.log

# System errors
sudo tail -100 /var/log/syslog
```

---

## Production Deployment Scripts

### Run Optimization Scripts

```bash
# Full server optimization (Redis, indexes, caching)
bash scripts/optimize_server.sh

# Setup Nginx + PHP-FPM
bash scripts/setup_nginx.sh

# Monitor performance during load
bash scripts/monitor_performance.sh
```

---

## Quick Workflows

### Starting Your Day

```bash
# Check services are running
sudo systemctl status nginx php8.4-fpm redis-server mysql

# Pull latest code
git pull origin main

# Update dependencies if needed
composer install
npm install

# Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache

# Check logs for issues
tail -50 storage/logs/laravel.log
```

### After Code Changes

```bash
# Clear caches
php artisan optimize:clear

# Restart PHP-FPM to reload code
sudo systemctl restart php8.4-fpm

# If routes changed
php artisan route:cache

# If config changed
php artisan config:cache

# Test the application
curl http://localhost:8000/login/cashier
```

### Running Load Tests

```bash
# Start performance monitoring in one terminal
bash scripts/monitor_performance.sh

# In another terminal, run JMeter test
cd jmeter-tests
jmeter -n -t POS_Supermarket.jmx -l results/test_$(date +%Y%m%d_%H%M%S).jtl -e -o results/report_$(date +%Y%m%d_%H%M%S)

# Review results
firefox results/report_*/index.html
```

---

## Environment Variables

Common `.env` settings you might need to change:

```bash
# Edit environment file
nano .env

# Key settings:
APP_ENV=production
APP_DEBUG=false
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# After changes, restart services:
php artisan config:cache
sudo systemctl restart php8.4-fpm
```

---

## Helpful Aliases (Optional)

Add to your `~/.bashrc` or `~/.bash_aliases`:

```bash
# Laravel shortcuts
alias pa='php artisan'
alias pao='php artisan optimize:clear'
alias pas='php artisan serve'
alias pam='php artisan migrate'

# Service shortcuts
alias nginx-restart='sudo systemctl restart nginx'
alias php-restart='sudo systemctl restart php8.4-fpm'
alias services-status='sudo systemctl status nginx php8.4-fpm redis-server mysql'

# Log viewers
alias laravel-log='tail -f storage/logs/laravel.log'
alias nginx-log='sudo tail -f /var/log/nginx/error.log'

# Git shortcuts
alias gs='git status'
alias gp='git pull'
alias gc='git commit -m'

# After adding, reload:
source ~/.bashrc
```

---

## Quick Reference Card

| Task | Command |
|------|---------|
| Start dev server | `php artisan serve` |
| Clear caches | `php artisan optimize:clear` |
| Run migrations | `php artisan migrate` |
| Restart services | `sudo systemctl restart nginx php8.4-fpm` |
| View logs | `tail -f storage/logs/laravel.log` |
| Run JMeter test | `jmeter -n -t POS_Supermarket.jmx -l results/test.jtl` |
| Check service status | `sudo systemctl status nginx` |
| Connect to MySQL | `mysql -u root -p` |
| Connect to Redis | `redis-cli` |
| Monitor performance | `bash scripts/monitor_performance.sh` |

---

**Pro Tip**: Keep this file open in a separate terminal or text editor for quick reference while working!
