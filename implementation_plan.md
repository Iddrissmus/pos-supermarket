# Invoice System Improvements Plan

## Goals
Implement advanced invoicing features including Recurring Invoices, Partial Payments, and Automated Reminders.

## 1. Database Schema Changes
Create a new migration to add necessary columns to the `invoices` table.

- **Recurring Support**:
  - `is_recurring` (boolean, default false)
  - `recurring_frequency` (enum: 'weekly', 'monthly', 'quarterly', 'yearly', nullable)
  - `recurring_end_date` (date, nullable)
  - `recurring_next_date` (date, nullable)
- **Partial Payment Support**:
  - `allow_partial_payment` (boolean, default false)

## 2. Models & Logic
- **Invoice Model**:
  - Add `recurring()` scope.
  - Add logic to calculate next recurring date.
  - Update `markAsPaid` to handle partial payments.
    - If paid < total, status could become 'partial' (if we add it) or remain 'sent' but `balance_due` decreases.
    - Update `payment_status` enum if needed: 'partial'

## 3. Recurring Invoice System
- **UI**: Add "Recurring" toggle and frequency options to Invoice Create/Edit page.
- **Backend**:
  - Create Artisan Command: `invoices:process-recurring`
  - Logic: Find active recurring invoices where `recurring_next_date` <= Today.
  - Action: Replicate the invoice (new number, resetting dates/status), save, dispatch "Invoice Created" email, update original invoice's `recurring_next_date`.

## 4. Part-Payment System
- **UI**:
  - Toggle "Allow Partial Payment" on Invoice Create/Edit.
  - Public Payment Page: If allowed, show "Amount to Pay" input (min: 1, max: balance_due). Default to balance_due.
- **Backend (PublicInvoiceController)**:
  - `pay()`: Accept `amount` parameter. Validate against balance.
  - `callback()`: Verify paid amount. Call `markAsPaid($amount)`.
    - If `balance_due > 0`: Status remains 'sent' (or 'partial'). Invoice remains open. Email says "Partial Payment Received. Remaining: X".
    - If `balance_due == 0`: Status 'paid'. Email "Payment Received".

## 5. Automated Reminders
- **Backend**:
  - Create Artisan Command: `invoices:send-reminders`
  - Logic: Find unpaid invoices due today or soon.
  - Send `InvoicePaymentReminder` email.

## 6. Execution Steps
1. [ ] Create Migration for new columns.
2. [ ] Update `Invoice` model.
3. [ ] Update `Invoices/Create` view.
4. [ ] Update `InvoiceController`.
5. [ ] Update `PublicInvoiceController` & Payment View.
6. [ ] Implement `ProcessRecurringInvoices` Command.
7. [ ] Implement `SendInvoiceReminders` Command.
