# Manager Item Requests - Workflow Explanation

**Page:** `/manager/item-requests`  
**Updated:** October 26, 2025

## Overview

This page allows **branch managers** to request stock transfers from other branches when they are running low on inventory.

## How It Works - 3-Step Process

### Step 1: Create New Stock Request (Blue Section)
**What it does:** Allows managers to submit a NEW request for stock items

- **Form Fields:**
  - **Product:** Choose which product you need
  - **From Branch:** Select which branch has the product in stock
  - **Quantity:** How many units you need
  - **Reason:** Optional explanation (e.g., "High customer demand", "Running low")

- **What happens when you click "Submit Request":**
  1. The request is created with status `pending`
  2. It moves to the "Pending Requests" section below
  3. An administrator is notified to review the request
  4. You wait for admin approval

---

### Step 2: Pending Requests (Yellow Section)
**What it does:** Shows requests WAITING for admin approval

This table displays:
- Requests you've already submitted
- They are in "pending" status
- Waiting for an administrator to approve/reject them

**Actions you can take:**
- **Cancel:** Remove the request if you no longer need it

**You CANNOT approve your own requests** - only admins can do that.

---

### Step 3: Recent Completed Requests (Green Section)
**What it does:** Shows your request HISTORY

This section displays:
- Approved requests (admin said yes)
- Completed requests (stock has been transferred)
- Last 5 completed transfers for reference

**This is read-only** - just for your records.

---

## Why Are There Two Sections?

Before the update, users were confused because:
- ❌ "Submit New Request" looked like it was for pending requests
- ❌ "Pending Requests" looked like another form to submit

Now it's clear:
- ✅ **Blue Section** = CREATE new requests (Step 1)
- ✅ **Yellow Section** = VIEW pending requests (Step 2)
- ✅ **Green Section** = VIEW history (Step 3)

---

## Example Workflow

**Scenario:** Your Accra Main branch is running low on Coca-Cola

1. **You submit a request:**
   - Go to Blue section "Create New Stock Request"
   - Select "Coca-Cola 500ml"
   - Choose "From Branch: Kumasi Branch" (they have 200 units)
   - Enter "Quantity: 50"
   - Enter "Reason: High customer demand"
   - Click "Submit Request"

2. **Request moves to pending:**
   - Request now appears in Yellow section "Pending Requests"
   - Status: "Pending"
   - You wait for admin approval
   - You can still cancel if needed

3. **Admin reviews and approves:**
   - Admin goes to `/requests/approval`
   - Reviews your request
   - Approves it
   - Stock transfer is executed

4. **Request is completed:**
   - Request moves to Green section "Recent Completed Requests"
   - Status: "Approved" or "Completed"
   - 50 units of Coca-Cola are transferred from Kumasi to Accra Main
   - Your inventory is updated automatically

---

## Visual Changes Made

### Before (Confusing):
```
┌─────────────────────────────────┐
│ Submit New Request              │  <- Looks basic
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ Pending Requests               │  <- Looks like another form?
└─────────────────────────────────┘
```

### After (Clear):
```
┌─────────────────────────────────┐
│ 🔵 Create New Stock Request     │
│ Step 1: Submit Request          │  <- Blue border, icon, badge
│ "Request stock items from..."   │  <- Description
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 🟡 Pending Requests             │
│ Step 2: Awaiting Admin Approval │  <- Yellow border, icon, badge
│ "These requests are waiting..." │  <- Description
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ 🟢 Recent Completed Requests    │
│ Step 3: Approved & Completed    │  <- Green border, icon, badge
│ "History of your approved..."   │  <- Description
└─────────────────────────────────┘
```

---

## Key Improvements

1. ✅ **Color-coded borders** (Blue → Yellow → Green) show the workflow progression
2. ✅ **Icons** make each section instantly recognizable
3. ✅ **Step badges** (Step 1/2/3) show the process flow
4. ✅ **Descriptions** explain what each section does
5. ✅ **Better empty state messages** guide users on what to do next

---

## For Your Presentation

**Demo Script:**
1. Show Blue section: "Here I can create new requests"
2. Show Yellow section: "Requests I submitted appear here while waiting"
3. Show Green section: "Once approved, they move here as history"
4. Emphasize: "Clear 3-step workflow with visual indicators"

This demonstrates **good UX design** and **clear information architecture**! 🎯
