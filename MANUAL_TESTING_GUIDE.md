# Manual Testing Guide (Comprehensive)

This document provides a complete verification checklist for every feature defined in the system. Use this as your definitive guide for Quality Assurance (QA).

## 1. Super Admin Role
**Credentials:** `superadmin@pos.com` / `password`

### A. Dashboard & Analytics
- [ ] **Access Dashboard**: Login -> Redirects to `/superadmin/dashboard`.
- [ ] **View Stats**: Verify 'Total Businesses', 'Total Users', 'Total Branches' cards satisfy database counts.
- [ ] **Log Viewer**: Go to `/superadmin/logs` -> Check valid log entries.

### B. Global Configurations (Settings)
- [ ] **General Settings**: Update Application Name/Timezone -> Save.
- [ ] **Paystack**: Enter Test Keys -> Click 'Test Connection' -> Verify Success.
- [ ] **SMS**: Configure Provider (e.g., mNotify) -> Send Test SMS.
- [ ] **Email**: Configure SMTP -> Send Test Email.

### C. Business Management
- [ ] **Create Business**: Quick Actions -> Create Business -> Fill Form -> Submit.
- [ ] **Edit Business**: Businesses List -> Edit 'FreshMart' -> Change Name -> Save.
- [ ] **Status Control**:
  - Block Business -> Verify it cannot log in.
  - Activate Business -> Verify access restored.
- [ ] **Branch Requests**: View pending new branch requests -> Approve/Reject.

### D. System User Management
- [ ] **Create Admin**: System Users -> Add New -> Role: 'SuperAdmin' -> Submit.
- [ ] **User Status**: Find user -> Click 'Block' -> Verify user logs out/cannot login.
- [ ] **Bulk Actions**: Select multiple users -> Bulk Delete.

---

## 2. Business Admin Role
**Credentials:** `businessadmin@pos.com` / `password`

### A. Dashboard & Reporting
- [ ] **Dashboard**: Verify charts load for *your* business data only.
- [ ] **Sales Reports**: Go to Reports -> Sales Report -> Filter by Date -> Export PDF.
- [ ] **Product Analytics**: Go to Product Reports -> Check 'Best Sellers' listing.
- [ ] **Activity Logs**: View logs -> Verify entries for recent actions.

### B. Inventory Management
- [ ] **Categories**: Create 'Snacks' -> Edit -> Delete.
- [ ] **Products (CRUD)**: Create 'Gari 1kg' -> Set Price -> Save.
- [ ] **Bulk Import**:
  - Download Template.
  - Fill Excel -> Upload.
  - Verify products appear in list.
- **Stock Receipts (Adding Stock)**:
  - New Receipt -> Select Branch/Supplier -> Add Items -> Submit.
  - Verify Stock Level increases.

### C. Staff Management (HR)
- [ ] **Create Staff**: Add 'John Cashier' -> Role: Cashier.
- [ ] **Assign Branch**: Assign 'John Cashier' to 'Accra Branch'.
- [ ] **Status**: Deactivate 'John Cashier' -> Verify login blocked.

### D. Workflow Approvals
- [ ] **Stock Requests**:
  - Go to Requests -> Incoming (from Managers).
  - Approve Request -> Verify Stock moves (Source Branch -> Destination).

---

## 3. Manager Role
**Credentials:** `manager@pos.com` / `password`

### A. Branch Operations
- [ ] **Dashboard**: Verify view is limited to *assigned branch*.
- [ ] **Staff Assignment**: View assigned cashiers. Re-assign cashier within branch if allowed.
- [ ] **Daily Sales**: View today's sales list.

### B. Stock Management
- [ ] **Local Products**:
  - Create 'Plantain Chips (Local)' -> Save.
  - **Verify**: Item marked as 'Local Supplier'.
- [ ] **Item Requests (Internal Transfer)**:
  - Request 'Coke' from 'Main Warehouse'.
  - **Verify**: Status 'Pending' -> waiting for Business Admin approval.
- [ ] **Cancel Request**: Find pending request -> Cancel.

---

## 4. Cashier Role
**Credentials:** `cashier@pos.com` / `password`

### A. POS Terminal (Sales)
- [ ] **Session Start**: Open Register -> Enter Opening Float (e.g., 50.00).
- [ ] **Catalog**: Search 'Water' -> Click to Add.
- [ ] **Cart Actions**:
  - Increase Qty (+).
  - Decrease Qty (-).
  - Remove Item (Trash icon).
- [ ] **Checkout (Cash)**: Pay -> Cash -> Enter Amount -> Complete.
- [ ] **Checkout (Paystack)**: Pay -> Paystack -> User enters card on popup -> Success.
- [ ] **Receipt**: Verify Receipt Modal opens -> Check details (Tax, Change).

### B. Cash Drawer
- [ ] **Manual Open**: Click 'Open Drawer' (if hardware connected/simulated).
- [ ] **Close Register**: End of Day -> Enter Closing Balance -> Submit.
