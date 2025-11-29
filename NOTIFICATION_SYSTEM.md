# Comprehensive Notification System

## Overview
Your POS Supermarket system now has a robust, real-time notification system designed specifically for business admins and managers to stay informed about critical business events.

---

## 📢 Notification Types

### 1. **Low Stock Alerts** 🔴 CRITICAL
**Who gets notified:** Managers, Business Admins (fallback)
**When:** Product stock falls below reorder level
**Details included:**
- Product name and SKU
- Branch name
- Current stock level
- Reorder level
- Quantity needed to restock
- Urgency level (Critical if stock = 0, Warning otherwise)
**Icon:** Exclamation Triangle (⚠️)
**Color:** Red (critical) / Yellow (warning)
**Action:** Links to Products page

### 2. **New Stock Received** 📦 INFO
**Who gets notified:** Business Admins, Branch Managers
**When:** Stock receipt is created/completed
**Details included:**
- Number of products received
- Branch name
- Total value of stock received
- Who received it
- Item count
**Icon:** Box (📦)
**Color:** Green
**Action:** Links to Stock Receipt details

### 3. **High-Value Sale** 💰 INFO
**Who gets notified:** Business Admins, Branch Managers
**When:** Sale exceeds GHS 500
**Details included:**
- Sale amount (GHS)
- Branch name
- Number of items sold
- Cashier name
- Payment method
**Icon:** Dollar Sign ($)
**Color:** Purple
**Action:** Links to Sale details

### 4. **Product Expiring Soon** ⚠️ WARNING/CRITICAL
**Who gets notified:** Business Admins, Branch Managers
**When:** Product approaching expiration date (30 days or less)
**Details included:**
- Product name and SKU
- Branch name
- Expiry date
- Days until expiry
- Current stock quantity
- Urgency (Critical ≤7 days, Warning ≤30 days)
**Icon:** Exclamation Triangle (⚠️)
**Color:** Red (≤7 days) / Orange (≤30 days)
**Action:** Links to Products page for that branch

### 5. **Stock Transfer Completed** 🔄 INFO
**Who gets notified:** 
- Sender branch manager
- Recipient branch manager
**When:** Stock transfer between branches is completed
**Details included:**
- Product name
- Quantity transferred
- From branch → To branch
- Whether you're sender or recipient
**Icon:** Exchange (🔄)
**Color:** Blue
**Action:** Links to Products page

### 6. **Daily Sales Summary** 📊 INFO
**Who gets notified:** Business Admins, Branch Managers
**When:** End of day (automatically scheduled)
**Details included:**
- Branch name
- Date
- Total revenue (GHS)
- Number of sales
- Performance rating (Excellent/Good/Normal)
- Top-selling products
**Icon:** Chart Line (📈)
**Color:** Green (excellent) / Blue (good) / Gray (normal)
**Action:** Links to Sales page filtered by date and branch

### 7. **Branch Request Created** 🏢 ACTION REQUIRED (SuperAdmin only)
**Who gets notified:** All SuperAdmins
**When:** Business admin requests new branch
**Details included:**
- Business name
- Branch name
- Location
- Requester name
**Icon:** Clipboard List
**Color:** Yellow
**Action:** Links to Branch Request details for approval

### 8. **Branch Request Approved** ✅ SUCCESS
**Who gets notified:** Requester (Business Admin)
**When:** SuperAdmin approves branch request
**Details included:**
- Branch name
- Branch ID
**Icon:** Check Circle
**Color:** Green
**Action:** Links to Branch Map

### 9. **Branch Request Rejected** ❌ INFO
**Who gets notified:** Requester (Business Admin)
**When:** SuperAdmin rejects branch request
**Details included:**
- Branch name
- Rejection reason (detailed)
**Icon:** Times Circle
**Color:** Red
**Action:** Links to create new branch request

---

## 🎯 Notification Bell Features

### Real-Time Updates
- Auto-refreshes every 30 seconds
- Shows unread count badge (red circle with number)
- Badge shows "9+" if more than 9 notifications

### Interactive Dropdown
- Click bell to see latest 5 notifications
- Unread notifications highlighted in blue
- Each notification shows:
  - Colored icon (matches notification type)
  - Title (bold)
  - Message description
  - Time ago (e.g., "2 minutes ago")
  - Urgency badge (Critical/Warning) if applicable

### Actions
- **Click notification** → Marks as read + redirects to relevant page
- **"Mark all read" button** → Clears all unread notifications
- **"View all notifications"** → Goes to full notification page

### Smart Routing
Every notification has a specific action:
- Low stock → Products inventory
- Stock received → Stock receipt details
- High-value sale → Sale receipt
- Product expiring → Products page
- Stock transfer → Products inventory
- Daily summary → Sales report (filtered)
- Branch requests → Request management page

---

## 📍 Where Notifications Appear

### Top Navigation Bar
**Business Admins & Managers:**
- Bell icon (white, in colored top bar)
- Red badge showing unread count
- Dropdown menu on click

**SuperAdmins:**
- Clipboard icon for branch requests
- Bell icon is NOT shown (they use branch requests badge)

### Notification Page
- Full list with pagination
- Filter by notification type
- Search functionality
- Bulk actions (mark all read, delete)

---

## 🔔 Notification Channels

All notifications use **database channel** for:
- ✅ Persistence (notifications stored in database)
- ✅ Historical record
- ✅ User can view anytime
- ✅ Mark as read/unread
- ✅ No email spam

**Email channel** is used ONLY for:
- Low stock alerts (managers need immediate attention)

---

## 🚀 Automatic Triggers

### Real-Time (Instant)
1. **Stock receipt completed** → Stock Received notification
2. **Sale > GHS 500** → High-Value Sale notification
3. **Stock falls below reorder level** → Low Stock Alert
4. **Stock transfer completed** → Both branches notified
5. **Branch request submitted** → SuperAdmins notified
6. **Branch request approved/rejected** → Requester notified

### Scheduled (Automated)
1. **Daily Sales Summary** → Every day at midnight (can be configured)
2. **Product Expiring Soon** → Daily check at 8 AM (can be configured)
3. **Weekly Inventory Report** → Every Monday (future feature)

---

## 💡 Smart Features

### Priority System
- **Critical** (Red badge): Immediate attention needed
  - Stock = 0
  - Product expires in ≤7 days
  
- **Warning** (Orange badge): Soon needs attention
  - Low stock (below reorder level)
  - Product expires in 8-30 days
  
- **Normal** (No badge): Informational
  - Stock received
  - Sales completed
  - Daily summaries

### Notification Grouping
- Multiple similar notifications are grouped
- Example: "3 products low on stock" instead of 3 separate notifications

### Auto-Expiry
- Read notifications auto-archive after 30 days
- Unread notifications remain indefinitely until read

### Notification Filtering
- Filter by type (sales, stock, transfers, etc.)
- Filter by urgency (critical, warning, normal)
- Filter by date range
- Search by product name, branch name

---

## 🎨 Visual Design

### Color Coding
- 🔴 **Red**: Critical issues (out of stock, expiring soon)
- 🟠 **Orange**: Warnings (low stock, approaching expiry)
- 🟢 **Green**: Positive events (stock received, good sales)
- 🔵 **Blue**: Information (transfers, standard updates)
- 🟣 **Purple**: Special events (high-value sales)
- ⚫ **Gray**: Routine information

### Icons (FontAwesome)
- `fa-exclamation-triangle`: Warnings/alerts
- `fa-box`: Stock/inventory
- `fa-dollar-sign`: Sales/money
- `fa-exchange-alt`: Transfers
- `fa-chart-line`: Reports/analytics
- `fa-clipboard-list`: Requests/tasks
- `fa-check-circle`: Approvals
- `fa-times-circle`: Rejections

---

## 🔧 Technical Implementation

### Notification Structure
```php
[
    'type' => 'low_stock',
    'title' => 'Low Stock Alert',
    'message' => 'Product XYZ is low on stock',
    'icon' => 'fa-exclamation-triangle',
    'color' => 'red',
    'urgency' => 'critical',
    'action_url' => '/products?branch_id=1',
    // Additional context data
]
```

### Database Storage
- Table: `notifications`
- Columns: id, type, notifiable_type, notifiable_id, data (JSON), read_at, created_at
- Indexed: notifiable_type + notifiable_id + read_at
- Soft deletes supported

### API Endpoints
- `GET /notifications/unread` → Latest 5 unread
- `POST /notifications/{id}/mark-read` → Mark single as read
- `POST /notifications/mark-all-read` → Mark all as read
- `GET /notifications` → Full paginated list
- `DELETE /notifications/{id}` → Delete notification

---

## 📊 Notification Analytics (Future)

### Planned Features
- Notification response time tracking
- Most actioned notification types
- Notification effectiveness metrics
- User engagement statistics
- Custom notification preferences per user
- Notification scheduling preferences
- Digest mode (bundle notifications)

---

## ⚙️ Configuration

### Thresholds (Configurable)
```php
// config/notifications.php
return [
    'high_value_sale_threshold' => 500, // GHS
    'low_stock_urgency_days' => 7,
    'expiry_warning_days' => 30,
    'expiry_critical_days' => 7,
    'daily_summary_time' => '00:00',
    'expiry_check_time' => '08:00',
];
```

### Notification Preferences (Per User)
- Enable/disable specific notification types
- Choose notification channels (database, email, SMS)
- Set quiet hours
- Configure notification frequency

---

## 🎯 Best Practices

### For Business Admins
✅ Check notifications at start of day
✅ Act on critical notifications immediately
✅ Review daily summaries for trends
✅ Set up email forwarding for critical alerts

### For Managers
✅ Prioritize critical stock alerts
✅ Monitor high-value sales
✅ Track daily performance summaries
✅ Respond to stock transfer requests promptly

### For SuperAdmins
✅ Review branch requests daily
✅ Monitor system-wide alerts
✅ Set up notification thresholds appropriately

---

## 🐛 Troubleshooting

**Problem:** Not receiving notifications
**Solution:** Check notification preferences, ensure you're assigned to correct branch/business

**Problem:** Too many notifications
**Solution:** Adjust thresholds in settings, enable digest mode

**Problem:** Notification count not updating
**Solution:** Hard refresh browser (Ctrl+F5), check network connection

---

## 🔮 Future Enhancements
1. Push notifications (mobile/desktop)
2. SMS notifications for critical alerts
3. WhatsApp integration
4. Slack/Teams integration
5. Custom notification rules builder
6. AI-powered notification prioritization
7. Notification templates customization
8. Multi-language support

---

**Your notification system is now live and actively monitoring your business! 🎉**
