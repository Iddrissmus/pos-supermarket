# POS Supermarket

A Laravel-based Point Of Sale (POS) application for managing businesses, branches, products and stock. This project includes an automatic reorder system per branch which creates pending reorder requests and a manager UI to review requests.

---

## 📚 Documentation

- **[USER_GUIDE.md](USER_GUIDE.md)** - Quick start guide for first-time users
- **[COMMANDS_REFERENCE.md](COMMANDS_REFERENCE.md)** - Complete command reference
- **[FEATURE_REVIEW_REPORT.md](FEATURE_REVIEW_REPORT.md)** - Feature implementation audit
- **[SECURITY_AUDIT.md](SECURITY_AUDIT.md)** - Security audit report

---

## Quick Links

- Pending reorder UI: `/reorder-requests` (login required)
- Artisan reorder check: `php artisan stock:check-reorder`

---

## Requirements

- **PHP 8.1+** (tested with PHP 8.3+)
- **Composer**
- **MySQL** (or SQLite for development)
- **Node.js + npm** (for frontend assets)
- **Nginx + PHP-FPM** (production) or php artisan serve (development)
- **Redis** (recommended for sessions and caching)

---

## Local Setup

### 1. Clone the Repository

```bash
git clone <repo-url> pos-supermarket
cd pos-supermarket
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:
- Database connection (MySQL or SQLite)
- Redis connection (if using Redis for sessions/cache)
- SMS service credentials (optional)

### 4. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Frontend Assets

```bash
npm run dev    # For development
npm run build  # For production
```

### 6. Start the Application

**Development (single user):**
```bash
php artisan serve
# Access at http://localhost:8000
```

**Production (concurrent users):**
```bash
# Use the provided setup script
bash scripts/setup_nginx.sh

# Or manually configure Nginx + PHP-FPM
# Server will run on http://localhost:8000 automatically
```

---

## Production Deployment

For production environments with multiple concurrent users:

### 1. Server Optimization

Run the optimization script:
```bash
bash scripts/optimize_server.sh
```

This will:
- Install and configure Redis
- Optimize Laravel caches (config, routes, views)
- Add database indexes
- Configure PHP opcache

### 2. Nginx + PHP-FPM Setup

```bash
bash scripts/setup_nginx.sh
```

This configures:
- Nginx web server on port 8000
- PHP-FPM with 10 workers (handles 10-20 concurrent requests)
- Redis sessions and caching
- Auto-start services on boot

### 3. Performance Monitoring

Monitor system resources during operation:
```bash
bash scripts/monitor_performance.sh
```

### 4. Managing Services

```bash
# Check service status
sudo systemctl status nginx php8.4-fpm redis-server

# Restart services
sudo systemctl restart nginx php8.4-fpm

# Stop services
sudo systemctl stop nginx php8.4-fpm

# Disable auto-start on boot
sudo systemctl disable nginx php8.4-fpm redis-server
```

---

## Important Artisan Commands

### Stock Management

```bash
# Run auto-reorder scan (creates pending transfer requests)
php artisan stock:check-reorder
```

### Cache Management

```bash
# Clear all caches
php artisan optimize:clear

# Or clear individually:
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Rebuild caches (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Testing

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Feature/ProductTest.php
```

---

## How the Auto-Reorder System Works

- Each branch has product assignments in `branch_products` (with `stock_quantity` and `reorder_level`)
- When stock changes, code calls `BranchProduct::adjustStock($delta, $action, $note)` which:
  - Updates `stock_quantity` (never below 0)
  - Creates a `stock_logs` entry
  - Runs reorder check via `App\Services\StockReorderService::checkItem($branchId, $productId)`
- The reorder check:
  - Triggers when `stock_quantity <= reorder_level` and `reorder_level > 0`
  - Creates `stock_logs` entry with `action = 'reorder_requested'` (max once per 24 hours)
  - Creates pending `stock_transfers` record if none exists

**Notes:**
- Pending reorder requests stored in `stock_transfers` table with `status = 'pending'`
- `from_branch_id` set to `REORDER_SOURCE_BRANCH_ID` from `.env` or branch ID
- Operations run in transactions to prevent race conditions

---

## User Roles & Permissions

- **SuperAdmin**: Full system access, manage all businesses
- **Business Admin**: Manage own business, branches, products, view reports
- **Manager**: Manage branch products, approve stock requests, view branch reports
- **Cashier**: Process sales at assigned branch only

---

## Testing

Tests are located in `tests/` directory:

```bash
# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage
```

Tests use `RefreshDatabase` trait for clean test environments.

---

## Performance Testing

JMeter test plans available in `jmeter-tests/`:

```bash
cd jmeter-tests
jmeter -n -t POS_Supermarket.jmx -l results/test.jtl -e -o results/report
```

Test configurations:
- 104 test users (30 cashiers, 25 managers, 25 business admins, 20 superadmins)
- Tests authentication, sessions, CSRF protection
- Baseline: 5 users (0% error, ~470ms avg)
- Load test: 100+ users with Nginx + PHP-FPM

---

## Troubleshooting

### PHP Memory Errors
- Increase `memory_limit` in `php.ini`
- Use queue workers for large operations
- Run scheduled commands with proper memory allocation

### Routes Not Found
```bash
php artisan route:clear
php artisan route:list
```

### Permission Errors (Nginx)
```bash
# Fix project permissions
sudo chown -R $USER:www-data /path/to/project
sudo chmod -R 755 /path/to/project
sudo chmod -R 775 storage bootstrap/cache

# Fix parent directory execute permissions
chmod +x ~
chmod +x ~/Projects
```

### Session/Cache Issues
```bash
php artisan optimize:clear
php artisan config:cache
sudo systemctl restart php8.4-fpm nginx
```

---

## Contribution Guide

- Follow **PSR-12** code style
- Add tests for new features
- Keep controllers thin, move business logic to services
- Use pagination for list endpoints
- Document breaking changes

---

## Project Structure

```
app/
├── Http/Controllers/    # Route controllers
├── Models/             # Eloquent models
├── Services/           # Business logic services
├── Livewire/          # Livewire components
├── Imports/           # Excel/CSV import handlers
└── Exports/           # Excel/CSV export handlers

resources/
├── views/             # Blade templates
├── js/                # JavaScript assets
└── css/               # CSS assets

database/
├── migrations/        # Database migrations
└── seeders/          # Database seeders

scripts/
├── setup_nginx.sh          # Production server setup
├── optimize_server.sh      # Performance optimization
└── monitor_performance.sh  # System monitoring

jmeter-tests/          # Load testing configuration
```

---

## Contact

For help or issues, please create an issue in the repository or contact the project maintainer.

---

**Thank you for using POS Supermarket!**
