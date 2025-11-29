# Manager Item Requests - Bulk Upload Feature

## Overview
Enhanced the manager item requests system to include comprehensive pricing information and bulk Excel upload capability. Managers can now request multiple stock items from other branches at once using a structured Excel template.

---

## Key Features

### 1. **Pricing Integration**
- All stock transfer requests now capture complete pricing information from source branch:
  - `price` - Base selling price
  - `cost_price` - Cost/purchase price
  - `price_per_kilo` - Weight-based pricing
  - `price_per_box` - Box-unit pricing
  - `weight_unit` - Unit of weight measurement
  - `price_per_unit_weight` - Price per unit weight

### 2. **Bulk Excel Upload**
- Request multiple items in a single operation
- Template-based structured input
- Comprehensive validation and error reporting
- Automatic stock availability checks

### 3. **Enhanced Validation**
- Prevents duplicate pending requests
- Validates stock availability in source branch
- Ensures managers cannot request from own branch
- Validates quantity constraints

---

## Database Changes

### Migration: `add_pricing_fields_to_stock_transfers_table`

**New Columns Added:**
```sql
- quantity_of_boxes (integer, nullable)
- quantity_per_box (integer, nullable)
- price (decimal 10,2, nullable)
- cost_price (decimal 10,2, nullable)
- price_per_kilo (decimal 10,2, nullable)
- price_per_box (decimal 10,2, nullable)
- weight_unit (string, nullable)
- price_per_unit_weight (decimal 10,2, nullable)
```

**Purpose:**
- Track pricing at time of request
- Support quantity breakdown (boxes × units)
- Enable accurate cost calculations
- Maintain pricing history for transfers

---

## Code Structure

### Models

#### **StockTransfer** (`app/Models/StockTransfer.php`)
**Updated Fillable Fields:**
```php
'quantity_of_boxes', 'quantity_per_box',
'price', 'cost_price', 'price_per_kilo', 
'price_per_box', 'weight_unit', 'price_per_unit_weight'
```

**Casts:**
```php
'price' => 'decimal:2',
'cost_price' => 'decimal:2',
'price_per_kilo' => 'decimal:2',
'price_per_box' => 'decimal:2',
'price_per_unit_weight' => 'decimal:2',
```

### Controllers

#### **ItemRequestController** (`app/Http/Controllers/Manager/ItemRequestController.php`)

**Key Methods:**

1. **store()** - Create single request
   - Fetches pricing from source BranchProduct
   - Validates stock availability
   - Checks for duplicate pending requests
   - Creates StockTransfer with pricing

2. **downloadTemplate()** - Download Excel template
   - Returns ItemRequestTemplateExport
   - Provides structured format with examples

3. **uploadBulkRequests()** - Process bulk upload
   - Validates file (xlsx/xls, max 2MB)
   - Uses ItemRequestImport class
   - Returns summary with success/error counts
   - Shows detailed error list if any

### Imports

#### **ItemRequestImport** (`app/Imports/ItemRequestImport.php`)

**Features:**
- Implements `ToCollection`, `WithHeadingRow`
- Processes rows sequentially with comprehensive validation
- Tracks success/skip counts
- Detailed error messages with row numbers

**Validation Checks:**
1. Product exists (by name or barcode)
2. Source branch exists and belongs to manager's business
3. Cannot request from own branch
4. Quantities > 0
5. Stock available in source branch
6. No duplicate pending requests

**Row Processing:**
```php
- product_name_or_barcode → Product lookup
- from_branch → Branch lookup
- quantity_of_boxes → Box count validation
- quantity_per_box → Units per box validation
- reason → Optional text (defaults to "Bulk request")
```

**Pricing Capture:**
- Automatically fetches pricing from source BranchProduct
- Includes all 6 pricing fields in StockTransfer
- Pricing captured at request time (historical record)

### Exports

#### **ItemRequestTemplateExport** (`app/Exports/ItemRequestTemplateExport.php`)

**Template Structure:**
- 5 columns with clear headers
- 5 sample rows demonstrating proper format
- Blue header styling (color: #4472C4)
- Alternating row colors for readability
- Borders and proper column widths

**Columns:**
1. **Product Name or Barcode** - Product identifier
2. **From Branch** - Source branch name
3. **Quantity of Boxes** - Number of boxes to request
4. **Units per Box** - Units contained in each box
5. **Reason (Optional)** - Justification for request

**Sample Data:**
- Milo 400g from Main Branch (5 boxes × 24 units)
- Peak Milk 400g from East Branch (10 boxes × 12 units)
- Rice (5kg) from West Branch (20 boxes × 10 units)
- Cooking Oil (1L) from North Branch (15 boxes × 12 units)
- Sugar (1kg) from Main Branch (8 boxes × 20 units)

---

## Routes

### New Routes Added (`routes/web.php`)

```php
// Download template
GET /manager/item-requests/download-template
→ ItemRequestController@downloadTemplate
→ Name: manager.item-requests.download-template

// Bulk upload
POST /manager/item-requests/bulk-upload
→ ItemRequestController@uploadBulkRequests
→ Name: manager.item-requests.bulk-upload
```

### Existing Routes (Context)
```php
GET /manager/item-requests → index
POST /manager/item-requests → store
PATCH /manager/item-requests/{stockTransfer}/cancel → cancel
```

---

## User Interface

### View Updates (`resources/views/manager/item-requests.blade.php`)

#### **1. Header Enhancement**
- Added "Bulk Request" button (indigo color)
- Opens bulk upload modal
- Positioned next to "Back to Dashboard"

#### **2. Success/Warning Messages**
- Enhanced to show import summary
- Collapsible error details list
- Warning alerts for partial successes
- Shows error count with expandable details

#### **3. Bulk Upload Modal**
- **Header:** Gradient indigo-to-purple with icon
- **Instructions:** Step-by-step guide
  1. Download template
  2. Fill in details
  3. Upload file
  4. Review results
- **Template Download:** Green button with download icon
- **Upload Form:**
  - File input (accepts .xlsx, .xls)
  - Max file size: 2MB
  - Shows expected column structure
  - Upload & Process button (indigo)
- **Expected Columns Display:**
  - Visual grid showing all 5 columns
  - Checkmarks for clarity
  - Optional indicator for Reason column

**Modal Design:**
- Responsive width (11/12 on mobile, 1/2 on desktop)
- Fixed overlay with backdrop
- Close button (X icon)
- Cancel and Submit actions
- Purple info box for column structure

---

## Workflow

### Single Request Workflow
1. Manager selects product from dropdown
2. Selects source branch (filtered by availability)
3. Enters quantity of boxes and units per box
4. System calculates total units
5. Shows available stock in source branch
6. Validates no duplicate pending request
7. Fetches pricing from source BranchProduct
8. Creates StockTransfer with status 'pending'

### Bulk Request Workflow
1. Manager clicks "Bulk Request" button
2. Modal opens with instructions
3. Manager downloads Excel template
4. Fills template with multiple requests
5. Uploads completed file
6. System validates each row:
   - Product exists
   - Branch exists and valid
   - Stock available
   - No duplicate requests
7. Creates StockTransfer for each valid row
8. Shows summary: X created, Y skipped
9. If errors, shows expandable error list with row numbers

### Approval Workflow (Existing - Business Admin)
1. Business Admin reviews pending requests
2. Checks source branch inventory
3. Approves or rejects with note
4. System updates StockTransfer status
5. If approved, inventory transferred

---

## Validation Rules

### Single Request
```php
'product_id' => 'required|exists:products,id'
'from_branch_id' => 'required|exists:branches,id'
'quantity_of_boxes' => 'required|integer|min:1'
'quantity_per_box' => 'required|integer|min:1'
'reason' => 'nullable|string|max:500'
```

### Bulk Upload
```php
'file' => 'required|mimes:xlsx,xls|max:2048'
```

### Row-Level Validation (Import)
- Product found (by name or barcode)
- Branch found in same business
- Not manager's own branch
- quantity_of_boxes > 0
- quantity_per_box > 0
- Sufficient stock in source branch
- No pending request for same product/branch

---

## Error Handling

### Import Errors Format
```
Row 2: Product 'ABC123' not found.
Row 3: Branch 'Unknown Branch' not found in your business.
Row 5: Insufficient stock in 'East Branch'. Available: 50, Requested: 100.
Row 7: You already have a pending request for 'Milo 400g' from 'Main Branch'.
```

### Success Messages
```
Import completed: 5 requests created successfully
Import completed: 3 requests created successfully, 2 rows skipped
```

### Exception Handling
- File validation errors (wrong format, too large)
- Database errors logged
- User-friendly error messages
- Rollback on critical failures

---

## Benefits

### For Managers
1. **Efficiency:** Request 10+ items in one upload vs manual form submission
2. **Accuracy:** Template structure prevents data entry errors
3. **Visibility:** Clear error reporting with row numbers
4. **Speed:** Bulk operations save significant time
5. **Planning:** Can prepare requests offline in Excel

### For Business
1. **Audit Trail:** Pricing captured at request time
2. **Cost Tracking:** Complete pricing information for analysis
3. **Historical Data:** Pricing preserved even if changed later
4. **Reporting:** Detailed request data for business intelligence
5. **Scalability:** Handles high-volume request scenarios

### For System
1. **Data Integrity:** Comprehensive validation prevents bad data
2. **Performance:** Single transaction for multiple requests
3. **Consistency:** Standardized import format
4. **Maintainability:** Follows existing bulk import patterns
5. **Extensibility:** Easy to add more columns/validation

---

## Testing Checklist

### Single Request
- ✅ Request with valid product and branch
- ✅ Pricing fields populated from source BranchProduct
- ✅ Duplicate request prevention
- ✅ Stock availability validation
- ✅ Cannot request from own branch

### Bulk Upload
- ✅ Download template generates proper Excel
- ✅ Valid file with all correct data processes successfully
- ✅ Invalid product names caught and skipped
- ✅ Invalid branch names caught and skipped
- ✅ Insufficient stock errors reported
- ✅ Duplicate requests detected
- ✅ Mixed success/error results handled properly
- ✅ Error list displayed with row numbers
- ✅ Success count accurate

### Edge Cases
- ✅ Empty rows skipped
- ✅ Partial file (some valid, some invalid)
- ✅ File too large rejected
- ✅ Wrong file format rejected
- ✅ Products with special characters
- ✅ Decimal quantities (should fail validation)

---

## File Locations

```
Controllers:
├── app/Http/Controllers/Manager/ItemRequestController.php

Models:
├── app/Models/StockTransfer.php

Imports:
├── app/Imports/ItemRequestImport.php

Exports:
├── app/Exports/ItemRequestTemplateExport.php

Migrations:
├── database/migrations/2025_11_29_203644_add_pricing_fields_to_stock_transfers_table.php

Views:
├── resources/views/manager/item-requests.blade.php

Routes:
├── routes/web.php (Manager middleware group)
```

---

## Future Enhancements

### Potential Improvements
1. **Excel Validation:** Pre-upload client-side validation
2. **Bulk Approval:** Allow admin to approve multiple requests at once
3. **Request Templates:** Save common request patterns
4. **Schedule Requests:** Set future request dates
5. **Auto-Reorder:** Automatic requests based on thresholds
6. **Pricing Override:** Allow managers to suggest prices
7. **Request History:** Track request patterns and trends
8. **Notifications:** Alert source branch of incoming requests
9. **Priority Levels:** Urgent vs standard requests
10. **Partial Approval:** Approve reduced quantities

### Analytics Opportunities
- Most requested products by branch
- Request-to-approval time metrics
- Stock availability patterns
- Pricing trends over time
- Manager request behavior analysis

---

## Summary

**What Changed:**
- StockTransfer model now stores complete pricing information
- Added quantity_of_boxes and quantity_per_box fields
- Created ItemRequestImport for bulk processing
- Created ItemRequestTemplateExport for template download
- Enhanced ItemRequestController with bulk methods
- Updated view with modal and bulk upload UI
- Added routes for template download and bulk upload

**Why It Matters:**
- Managers save time with bulk operations
- Pricing captured for accurate cost tracking
- Better audit trail and historical data
- Improved data quality through validation
- Scalable for high-volume operations

**How It Works:**
- Template-based Excel upload
- Comprehensive row-level validation
- Automatic pricing capture from source
- Detailed error reporting
- Maintains all existing single-request functionality

This enhancement modernizes the item requests feature while maintaining backward compatibility and following established patterns in the codebase.
