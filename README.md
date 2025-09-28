# POS Supermarket

A small Laravel-based Point Of Sale (POS) application for managing businesses, branches, products and stock. This project includes an automatic reorder system per branch which creates pending reorder requests (stored in `stock_transfers` with `status = 'pending'`) and a manager UI to review requests.

This README contains everything a new developer or contributor needs to get started on Windows (XAMPP) and development workflows.

---

## Quick links

- Pending reorder UI: /reorder-requests (login required)
- Artisan reorder check: `php artisan stock:check-reorder`

---

## Requirements

- PHP 8.1+ (project tested with PHP 8.3)
- Composer
- SQLite (bundled) or MySQL (XAMPP)
- Node.js + npm (for frontend assets)
- XAMPP (on Windows) or any local LAMP/LEMP stack

---

## Local Setup (Windows + XAMPP)

1. Clone the repository:

```bash
git clone <repo-url> pos-supermarket
cd pos-supermarket
```

2. Install Composer dependencies:

```bash
composer install
```

3. Copy the environment file and generate app key:

```bash
copy .env.example .env
php artisan key:generate
```

4. Configure your `.env` database. You can use the included SQLite file for quick local testing:

- Using SQLite (recommended for quick start):
	- `DB_CONNECTION=sqlite`
	- Create `database/database.sqlite` (empty file) and ensure `DB_DATABASE` points to it. The `.env.example` in this repo may already be set accordingly.

- Using MySQL (XAMPP):
	- Start MySQL via XAMPP control panel
	- Create a database (for example `pos_supermarket`)
	- Update `.env` with your DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD

5. Migrate the database and seed (when seeds are provided):

```bash
php artisan migrate --seed
```

6. Install frontend dependencies and build assets (optional):

```bash
npm install
npm run dev
```

7. Serve the application (development):

```bash
php artisan serve
```

Open `http://127.0.0.1:8000` in your browser.

---

## Important artisan commands

- Run the auto-reorder scan (scans all branch_products and creates pending transfers):

```bash
php artisan stock:check-reorder
```

- Run all tests:

```bash
vendor\\bin\\phpunit
```

- Clear cache, config, routes (useful while developing):

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

---

## How the auto-reorder system works

- Each branch has product assignments stored in `branch_products` (columns include `stock_quantity` and `reorder_level`).
- When stock changes, code should call `BranchProduct::adjustStock($delta, $action, $note)` which:
	- Updates the `stock_quantity` for that branch/product (never below 0)
	- Creates a `stock_logs` entry recording the change
	- Runs a lightweight per-item reorder check via `App\\Services\\StockReorderService::checkItem($branchId, $productId)` which:
		- Checks if `stock_quantity <= reorder_level` and `reorder_level > 0`
		- Creates a `stock_logs` entry with `action = 'reorder_requested'` if one hasn't been created in the last 24 hours
		- Creates a pending `stock_transfers` record with `status = 'pending'` if none already exists for that branch/product

Notes & choices made:
- Pending reorder requests are stored in `stock_transfers` (reusing that table for simplicity). The `from_branch_id` field is set to `REORDER_SOURCE_BRANCH_ID` from `.env` if configured, otherwise set to the branch id.
- The `checkItem()` method is deliberately lightweight and runs inside a transaction to avoid race conditions.
- A scheduled command is available (`php artisan stock:check-reorder`) that does a full scan using a DB cursor (memory-efficient) and will create requests if needed. This is safe to schedule hourly in production cron.

---

## Manager UI

- Managers can view pending requests at `/reorder-requests` (must be logged in). The list is paginated to avoid large memory usage.
- A quick link is available in the sidebar under "Messages".

---

## Performance & scaling notes

- The project avoids loading entire tables into memory for large operations by using `cursor()` and pagination where appropriate.
- Heavy or long-running tasks should be moved to queues (Laravel queues + workers). If you expect a large number of sales/updates, dispatch reorder checks as jobs rather than running them inline.
- Add DB indexes on frequently filtered columns for large datasets:
	- `branches.manager_id`
	- `branch_products.branch_id`, `branch_products.product_id`
	- `stock_transfers.to_branch_id`, `stock_transfers.product_id`, `stock_transfers.status`

---

## Testing

- Tests live in the `tests/` directory. Run them with:

```bash
vendor\\bin\\phpunit
```

- Use `RefreshDatabase` in tests to run migrations in-memory or on SQLite test DB.

---

## Troubleshooting

- PHP memory errors during large scans: check you are running the scheduled command in production with a worker and/or increase `memory_limit` in `php.ini` or use queue workers so web requests are not impacted.
- If routes not found, run:

```bash
php artisan route:clear
php artisan route:list
```

---

## Contribution guide

- Follow PSR-12 code style.
- Add tests for any new behavior. Keep controllers thin and move business logic into services.
- Use pagination or cursors for any list endpoints.

---

## Contact

If you need help, add an issue describing your problem, or reach out to the repository owner.

---

Thank you for using POS Supermarket!
