# Role Permissions Documentation

## Overview
This document clearly defines the permissions and responsibilities for each user role in the POS Supermarket System.

---

## 🔴 SuperAdmin (System Administrator)

### Purpose
Create and manage multiple businesses and system-wide settings. **CANNOT** manage day-to-day operations.

### Permissions
✅ **CAN:**
- Create multiple businesses
- Assign Business Administrators to businesses
- Manage system-wide settings
- View all businesses in the system
- Create and assign all user roles
- Access system-wide analytics and reports

❌ **CANNOT:**
- Manage individual branch operations
- Add inventory to specific branches
- Process sales transactions
- Manage day-to-day business activities
- Access POS terminal

### Dashboard Features
- List of all businesses
- System-wide statistics (total businesses, branches, users)
- Business creation and management
- System configuration

---

## 🔵 Business Admin (Business Administrator)

### Purpose
Manage their assigned business only. Create branches, assign managers, and view business-wide reports. **CANNOT** create new businesses.

### Scope
- Limited to their assigned business only
- Cannot access other businesses
- Cannot create new businesses (only SuperAdmin can)

### Permissions
✅ **CAN:**
- Create and manage branches within their business
- Assign managers to branches
- Create and manage staff (managers, cashiers)
- Add regular inventory to the system
- Manage suppliers
- Manage products and categories
- Approve manager requests (stock transfers, etc.)
- View business-wide reports
- Manage customer database
- View all branches' performance

❌ **CANNOT:**
- Create new businesses
- Access other businesses' data
- Process sales transactions
- Access POS terminal
- Manage day-to-day branch operations

### Dashboard Features
- Business overview statistics
- All branches in their business
- Staff management (create managers, cashiers)
- Inventory management
- Request approval system
- Business-wide reports and analytics

---

## 🟢 Manager

### Purpose
Focus on day-to-day branch operations. Manage staff schedules, monitor daily sales. **CANNOT** add regular inventory (only Business Admin can).

### Scope
- Limited to their assigned branch only
- Cannot access other branches
- Cannot add products to the system

### Permissions
✅ **CAN:**
- Monitor daily sales at their branch
- Manage staff schedules
- Assign cashiers to shifts
- Request stock transfers from Business Admin
- View branch-level reports
- Manage customer interactions
- Request emergency items (non-central suppliers)
- View low stock alerts for their branch

❌ **CANNOT:**
- Add regular inventory (only Business Admin can)
- Create or modify products
- Access other branches
- Process sales transactions (cashier-only)
- Approve their own requests
- Access business-wide reports
- Create new staff accounts (can only assign existing cashiers)

### Dashboard Features
- Branch daily sales overview
- Staff schedule management
- Low stock alerts
- Request management (submit and track)
- Customer management

---

## 🟠 Cashier

### Purpose
**ONLY** role that can make sales at the POS terminal. Process transactions only. **CANNOT** access reports or management features.

### Scope
- Limited to POS terminal
- Can only process sales
- Assigned to specific branch

### Permissions
✅ **CAN:**
- Process sales transactions at POS terminal
- Handle payments (cash, card, etc.)
- Print receipts
- View product availability during sale
- Process returns/refunds (if authorized)
- View their sales history

❌ **CANNOT:**
- Access any reports
- View inventory levels (except during checkout)
- Manage products or prices
- Access management dashboards
- Create or modify customer data
- Approve or deny anything
- Access other branches
- Modify their own sales after completion

### Dashboard Features
- POS terminal interface
- Quick access to products
- Sales history (their own transactions only)
- Simple checkout interface

---

## Permission Matrix

| Feature | SuperAdmin | Business Admin | Manager | Cashier |
|---------|-----------|----------------|---------|---------|
| **Business Management** |
| Create businesses | ✅ | ❌ | ❌ | ❌ |
| Manage own business | ✅ | ✅ | ❌ | ❌ |
| Create branches | ✅ | ✅ | ❌ | ❌ |
| **User Management** |
| Create any user role | ✅ | ❌ | ❌ | ❌ |
| Create staff (managers/cashiers) | ❌ | ✅ | ❌ | ❌ |
| Assign managers to branches | ❌ | ✅ | ❌ | ❌ |
| Assign cashiers to shifts | ❌ | ✅ | ✅ | ❌ |
| **Inventory Management** |
| Add regular inventory | ❌ | ✅ | ❌ | ❌ |
| Create products | ❌ | ✅ | ❌ | ❌ |
| Manage suppliers | ❌ | ✅ | ❌ | ❌ |
| Request stock transfers | ❌ | ❌ | ✅ | ❌ |
| Approve stock requests | ❌ | ✅ | ❌ | ❌ |
| **Sales** |
| Process sales at POS | ❌ | ❌ | ❌ | ✅ |
| Monitor daily sales | ❌ | ✅ | ✅ | ❌ |
| **Reports** |
| System-wide reports | ✅ | ❌ | ❌ | ❌ |
| Business-wide reports | ❌ | ✅ | ❌ | ❌ |
| Branch-level reports | ❌ | ✅ | ✅ | ❌ |
| Sales reports | ❌ | ✅ | ✅ | ❌ |
| **Customer Management** |
| Manage customers | ❌ | ✅ | ✅ | ❌ |
| View customers during sale | ❌ | ❌ | ❌ | ✅ |

---

## Route Protection

### SuperAdmin Routes
```
/superadmin/dashboard
/businesses (CRUD)
```

### Business Admin Routes
```
/business-admin/dashboard
/admin/branch-assignments
/admin/cashiers
/requests/approval
/product (CRUD)
/suppliers (CRUD)
/stock-receipts (CRUD)
/business-admin/reports
```

### Manager Routes
```
/manager/dashboard
/manager/cashiers (assign only)
/manager/item-requests
/manager/daily-sales
/reorder-requests
```

### Cashier Routes
```
/cashier/dashboard
/sales (create, store, show only)
/terminal
/sales/{id}/receipt
```

---

## Implementation Checklist

- [x] Role constants defined in User model
- [x] Database migration for business_id
- [x] Route middleware protection
- [x] SuperAdmin dashboard created
- [x] Business Admin dashboard created
- [x] Manager routes limited to branch operations
- [x] Cashier routes limited to POS only
- [x] Removed report access from cashiers
- [x] Removed inventory management from managers
- [x] Product management restricted to Business Admin only

---

## Testing Accounts

| Role | Email | Password | Access |
|------|-------|----------|--------|
| SuperAdmin | superadmin@pos.com | password123 | System-wide |
| Business Admin | businessadmin@pos.com | password | Business #1 |
| Manager | manager@pos.com | password | Branch #1 |
| Cashier | cashier@pos.com | password | POS Terminal |

---

## Notes

1. **SuperAdmin** is system-level only - they set up businesses but don't run them
2. **Business Admin** runs the business but cannot create new businesses
3. **Manager** handles day-to-day operations at branch level only
4. **Cashier** can ONLY process sales - no management access at all
5. Sales can ONLY be created by cashiers - no other role can access POS
6. Inventory can ONLY be added by Business Admin - managers can only request
