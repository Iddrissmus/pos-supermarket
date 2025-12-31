# System Verification Checklist

## 1. Super Admin (Setup)
**Login:** `superadmin@pos.com` / `password`

- [ ] **Create Business**
  - Go to **Quick Actions > Create Business**.
  - Name: `Test Biz`, Owner Email: `owner@test.com`, Phone: `0555555555`.
  - **Verify:** Redirects to business details.
- [ ] **Create Branch**
  - In Business Details, click **Add Branch** (bottom).
  - Name: `Main Branch`, Is Main: `Yes`.
  - **Verify:** Branch appears in list.
- [ ] **Paystack Config**
  - Go to **Settings > Paystack**.
  - Click **Test Connection**.
  - **Verify:** "Connection Successful" message.

## 2. Business Admin (Inventory)
**Login:** `businessadmin@pos.com` / `password`

- [ ] **Create Category**
  - **Inventory > Categories > Add**.
  - Name: `Drinks`.
  - **Verify:** Category listed.
- [ ] **Create Product**
  - **Inventory > Products > Add**.
  - Name: `Coke`, Category: `Drinks`, Alert Qty: `5`.
  - **Verify:** Product listed (Stock: 0).
- [ ] **Receive Stock**
  - **Inventory > Stock Receipts > New**.
  - Supplier: [General](file:///home/iddrissmus/Projects/pos-supermarket/app/Http/Controllers/SuperAdmin/SettingsController.php#74-139), Product: `Coke`.
  - Box Qty: `10`, Per Box: `24`, Unit Cost: `2.00`.
  - **Verify:** Success message, Stock = 240.

## 3. Manager (Requests)
**Login:** `manager@pos.com` / `password`

- [ ] **Request Item**
  - **Stock Transfers > Request Item**.
  - From: `Main Branch`, Product: `Coke`, Boxes: `2`.
  - **Verify:** Request submitted (Pending).
- [ ] **Check Stock**
  - **Inventory > Stock Levels**.
  - **Verify:** View stock for your specific branch only.

## 4. Cashier (POS & Sales)
**Login:** `cashier@pos.com` / `password`

- [ ] **Open Register**
  - Enter Opening Amount: `100.00` -> **Open**.
- [ ] **Cash Sale**
  - Search `Coke` -> Add to Cart (x2).
  - Click **Pay** -> **Cash**.
  - Tender: `20.00`.
  - **Verify:** Change calculated, Receipt modal appears.
- [ ] **Paystack Sale**
  - Add to Cart -> **Pay** -> **Paystack**.
  - Select **Card** -> Use Test Card -> Success.
  - **Verify:** Sale completes automatically.
