# POS Supermarket - User Guide

**Quick Start Guide for First-Time Users**

---

## 🚀 Getting Started

### First Time Setup

1. **Access the Application**
   - Open your web browser
   - Navigate to: `http://localhost:8000` (or your server URL)
   - You'll see the landing page

2. **Initial Setup** (First Time Only)
   - Click "Setup" or go to `/setup`
   - Configure your database connection
   - Import the database bundle
   - Create your SuperAdmin account
   - You'll be automatically logged in

---

## 👥 User Roles & Login

### SuperAdmin
- **Login URL**: `/login/superadmin`
- **Access**: Full system control
- **Key Features**:
  - Manage all businesses
  - Approve branch requests
  - View notifications (bell icon)
  - Quick access to branch requests (clipboard icon)
  - System settings & configuration
  - View system-wide logs

### Business Admin
- **Login URL**: `/login/business-admin`
- **Access**: Manage your business
- **Key Features**:
  - Manage branches & products
  - View business reports
  - Manage staff (cashiers & managers)
  - Approve stock transfer requests

### Manager
- **Login URL**: `/login/manager`
- **Access**: Manage your branch
- **Key Features**:
  - View branch inventory
  - Request stock from other branches
  - View branch sales reports
  - Manage branch cashiers

### Cashier
- **Login URL**: `/login/cashier`
- **Access**: Process sales at your branch
- **Key Features**:
  - Open/close cash drawer
  - Process sales transactions
  - View your sales history

---

## 💰 Cashier Workflow (Daily Operations)

### Starting Your Shift

1. **Login** → Go to `/login/cashier`
2. **Open Cash Drawer**:
   - Enter opening cash amount
   - Add optional notes
   - Click "Open Drawer"
3. **Start Processing Sales**:
   - Go to POS Terminal (`/terminal`)
   - Select products from catalog
   - Add items to cart
   - Choose payment method (Cash/Card/Mobile Money)
   - Enter amount tendered
   - Complete sale

### Ending Your Shift

1. **Close Cash Drawer**:
   - Click "Close Drawer" button in terminal
   - Enter actual cash amount counted
   - Review difference (over/short)
   - Add closing notes (optional)
   - Click "Close Drawer"
2. **Review Summary**:
   - Opening amount
   - Cash sales total
   - Expected amount
   - Actual amount
   - Difference

---

## 📦 Manager Workflow

### Daily Tasks

1. **Check Notifications**:
   - Click bell icon (top right)
   - Review low stock alerts
   - Check stock transfer requests

2. **Request Stock**:
   - Go to "Item Requests"
   - Select product & source branch
   - Enter quantity
   - Submit request
   - Wait for Business Admin approval

3. **Bulk Stock Request**:
   - Click "Bulk Request" button
   - Download Excel template
   - Fill in multiple products
   - Upload completed file

4. **View Reports**:
   - Sales reports (branch performance)
   - Product reports (inventory status)
   - Daily sales summaries

---

## 🏢 Business Admin Workflow

### Managing Your Business

1. **Manage Branches**:
   - View all branches
   - Request new branch (requires SuperAdmin approval)
   - View branch map

2. **Manage Products**:
   - Create products
   - Assign products to branches
   - Set stock levels & reorder points
   - Bulk import products (Excel)

3. **Manage Staff**:
   - Create cashiers & managers
   - Assign staff to branches
   - Activate/deactivate accounts

4. **Approve Requests**:
   - Review pending stock transfer requests
   - Approve or reject requests
   - View request history

5. **View Reports**:
   - Business-wide sales reports
   - Product performance analytics
   - Activity logs

---

## 👑 SuperAdmin Workflow

### System Management

1. **Manage Businesses**:
   - View all businesses
   - Create/edit businesses
   - Activate/disable businesses

2. **Approve Branch Requests**:
   - Click clipboard icon (top right) for quick access
   - Or view branch request notifications via bell icon
   - Review pending requests
   - Approve or reject with reason

3. **Approve Business Signups**:
   - Review business signup requests
   - Approve or reject new businesses

4. **System Settings**:
   - General settings
   - SMS configuration
   - Email configuration
   - Payment gateway setup

5. **System Users**:
   - Manage all system users
   - Activate/deactivate accounts
   - View user activity

---

## 🔔 Notifications

### Notification Types

- **Low Stock**: Product stock below reorder level
- **Stock Received**: New stock received at branch
- **High-Value Sale**: Sale exceeding GHS 500
- **Stock Transfer**: Stock transfer completed
- **Branch Request**: New branch request (SuperAdmin)
- **Daily Summary**: End-of-day sales summary

### How to Use

1. **View Notifications**: 
   - **SuperAdmin, Business Admin, Manager**: Click bell icon (top right)
   - **SuperAdmin**: Also has clipboard icon for branch requests
2. **Mark as Read**: Click on notification
3. **View All**: Click "View all notifications" link
4. **Mark All Read**: Click "Mark all read" button

### Who Gets Notifications

- **SuperAdmin**: Branch requests, system notifications
- **Business Admin**: Low stock, stock received, high-value sales, stock transfers, daily summaries
- **Manager**: Low stock, stock received, high-value sales, stock transfers, daily summaries
- **Cashier**: No notifications (focus on sales terminal)

---

## 📊 Key Features

### Sales Terminal
- **Location**: `/terminal` (Cashiers only)
- **Features**:
  - Product catalog with search
  - Category filtering
  - Real-time cart calculation
  - Tax calculation
  - Multiple payment methods
  - Receipt generation

### Stock Management
- **Receive Stock**: Record stock receipts from suppliers
- **Stock Transfers**: Request stock from other branches
- **Auto-Reorder**: Automatic reorder requests when stock is low
- **Stock Logs**: Complete audit trail

### Reports
- **Sales Reports**: Filter by date, branch, cashier
- **Product Reports**: Performance, movement, profitability
- **Export**: CSV and PDF export available

---

## ⚠️ Common Tasks

### For Cashiers
- ✅ Open cash drawer before first sale
- ✅ Close cash drawer at end of shift
- ✅ Verify cash amount matches expected

### For Managers
- ✅ Check notifications daily
- ✅ Request stock when running low
- ✅ Review daily sales summaries

### For Business Admins
- ✅ Review and approve stock requests
- ✅ Monitor low stock alerts
- ✅ Review business reports regularly

### For SuperAdmins
- ✅ Check notifications regularly (bell icon)
- ✅ Review branch requests promptly (clipboard icon)
- ✅ Monitor system health
- ✅ Configure system settings

---

## 🆘 Troubleshooting

### Can't Login?
- Verify you're using the correct login URL for your role
- Check your email and password
- Contact your administrator if account is locked

### Cash Drawer Won't Open?
- Ensure you don't already have an open session today
- Check that you're assigned to a branch
- Contact manager if issues persist

### Can't Process Sale?
- Verify cash drawer is open
- Check product has stock
- Ensure you're at the correct branch

### Notifications Not Showing?
- Refresh the page (Ctrl+F5)
- Check your role has notification access
- Verify you're assigned to correct branch/business

---

## 📱 Quick Links

- **Landing Page**: `/`
- **Cashier Terminal**: `/terminal`
- **Sales Reports**: `/sales/report`
- **Notifications**: `/notifications`
- **Products**: `/product`
- **Stock Receipts**: `/stock-receipts`

---

## 💡 Tips

1. **Use Search**: Most pages have search functionality - use it!
2. **Keyboard Shortcuts**: Tab through forms, Enter to submit
3. **Notifications**: Check bell icon regularly for important updates
4. **Reports**: Export reports for offline analysis
5. **Bulk Operations**: Use Excel templates for bulk imports

---

**Need Help?** Contact your system administrator or refer to the main README.md for technical details.

