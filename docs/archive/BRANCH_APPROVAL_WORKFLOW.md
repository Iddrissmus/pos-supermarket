# Branch Approval Workflow

## Overview
This document describes the branch approval workflow implemented to provide checks and balances for branch creation in the POS Supermarket system.

## Workflow Description

### 1. Business Admin Submits Branch Request
- **Who**: Business Admin
- **Action**: Fills out branch creation form at `/branches/create`
- **Form Fields**:
  - Branch Name (required)
  - Location/Region (required)
  - Address (required)
  - Phone (optional)
  - Email (optional)
  - Coordinates (optional - from map)
  - Additional Notes (optional - visible to business admin only)
- **Result**: 
  - Creates `BranchRequest` record with status: `pending`
  - All superadmins receive notification
  - Business admin sees success message: "Branch request submitted successfully! Waiting for superadmin approval."

### 2. SuperAdmin Receives Notification
- **Who**: All SuperAdmins
- **Notification Channel**: Database notifications
- **Notification Badge**: Red badge on top bar showing pending request count
- **Notification Details**:
  - Business name
  - Branch name
  - Location
  - Requested by (business admin name)
  - Link to request details

### 3. SuperAdmin Reviews Request
- **Location**: `/branch-requests` (accessible from sidebar)
- **View Options**:
  - List view with filters: Pending, Approved, Rejected, All
  - Detail view showing complete request information
- **Information Displayed**:
  - Business information (name, admin)
  - Request information (requester, date)
  - Proposed branch details (name, location, address, phone, email, coordinates)
  - Additional notes (if provided)

### 4. SuperAdmin Decision

#### Option A: Approve Request
- **Action**: Click "Approve Request" button
- **System Processes**:
  1. Creates new `Branch` record in database
  2. Updates `BranchRequest` status to `approved`
  3. Records reviewer ID and timestamp
  4. Sends notification to requester
- **Notification to Business Admin**:
  - Title: "Branch Request Approved"
  - Message: "Your branch request for [Branch Name] has been approved!"
  - Includes link to branch map view
  - Includes branch ID for reference

#### Option B: Reject Request
- **Action**: Click "Reject Request" button → Modal opens
- **Required Input**: Rejection reason (textarea, max 1000 chars)
- **System Processes**:
  1. Updates `BranchRequest` status to `rejected`
  2. Stores rejection reason
  3. Records reviewer ID and timestamp
  4. Sends notification to requester
- **Notification to Business Admin**:
  - Title: "Branch Request Rejected"
  - Message: "Your branch request for [Branch Name] has been rejected."
  - Includes rejection reason
  - Includes link to create new request

### 5. Business Admin Receives Result
- **Notification Channel**: Database notifications
- **If Approved**:
  - Can view new branch on map
  - Can start assigning products to branch
  - Can assign managers/cashiers to branch
- **If Rejected**:
  - Can view rejection reason
  - Can submit new request with corrections
  - Can contact superadmin for clarification

## Database Structure

### branch_requests Table
```
- id (bigint, primary key)
- business_id (foreign key to businesses)
- requested_by (foreign key to users)
- branch_name (string, 255)
- location (string, 255)
- address (string, 500)
- phone (string, 50)
- email (string, 255)
- latitude (decimal 10,7)
- longitude (decimal 10,7)
- status (enum: pending, approved, rejected)
- reviewed_by (foreign key to users, nullable)
- reviewed_at (datetime, nullable)
- rejection_reason (text, nullable)
- notes (text, nullable)
- created_at (datetime)
- updated_at (datetime)
```

## Routes

### SuperAdmin Routes
```
GET  /branch-requests                           - List all requests
GET  /branch-requests/{branchRequest}           - View request details
POST /branch-requests/{branchRequest}/approve   - Approve request
POST /branch-requests/{branchRequest}/reject    - Reject request (requires rejection_reason)
```

### Business Admin Routes
```
POST /branches                                  - Create branch (becomes request if business_admin)
```

## Notifications

### BranchRequestCreated
- **Sent to**: All SuperAdmins
- **Trigger**: Business admin submits request
- **Data**:
  - type: 'branch_request'
  - title: 'New Branch Request'
  - message: '[Business Name] has requested to create a new branch'
  - branch_request_id
  - business_name
  - branch_name
  - location
  - action_url: Link to request details

### BranchRequestApproved
- **Sent to**: Requester (Business Admin)
- **Trigger**: SuperAdmin approves request
- **Data**:
  - type: 'branch_approved'
  - title: 'Branch Request Approved'
  - message: 'Your branch request for [Branch Name] has been approved!'
  - branch_request_id
  - branch_id
  - branch_name
  - action_url: Link to branch map

### BranchRequestRejected
- **Sent to**: Requester (Business Admin)
- **Trigger**: SuperAdmin rejects request
- **Data**:
  - type: 'branch_rejected'
  - title: 'Branch Request Rejected'
  - message: 'Your branch request for [Branch Name] has been rejected.'
  - branch_request_id
  - branch_name
  - rejection_reason
  - action_url: Link to create new request

## UI Components

### SuperAdmin Sidebar
- New menu item: "Branch Requests" (icon: clipboard-list)
- Location: Under "System Users"

### SuperAdmin Top Bar
- Branch requests badge (icon: clipboard-list)
- Shows count of pending requests
- Red notification badge if count > 0
- Clicking opens pending requests list

### Branch Request List View
- Tabs: Pending, Approved, Rejected, All
- Columns: Business, Branch Name, Location, Requested By, Status, Date, Actions
- Status badges: Yellow (Pending), Green (Approved), Red (Rejected)
- Pagination: 20 items per page

### Branch Request Detail View
- Three sections:
  1. Business Information (name, admin)
  2. Request Information (requester, date)
  3. Proposed Branch Details (all form fields)
- Review section (if processed): Reviewer, review date, rejection reason
- Action buttons (if pending): "Approve Request", "Reject Request"

### Reject Modal
- Textarea for rejection reason (required)
- Character limit: 1000
- Buttons: "Confirm Rejection", "Cancel"

## SuperAdmin vs Business Admin Behavior

### SuperAdmin
- Creating branch: Direct creation (no approval needed)
- Form submission: Goes to `/branches/store` → Creates `Branch` directly
- Success message: "Branch created successfully!"

### Business Admin
- Creating branch: Requires approval
- Form submission: Goes to `/branches/store` → Creates `BranchRequest`
- Success message: "Branch request submitted successfully! Waiting for superadmin approval."
- Additional field: "Notes" textarea (only shown to business admins)

## Testing the Workflow

1. **Login as Business Admin**
   - Navigate to Businesses → View Business → Add Branch
   - Fill form with branch details
   - Add notes explaining why branch is needed
   - Submit request
   - Verify success message

2. **Login as SuperAdmin**
   - Check top bar badge for pending count
   - Click badge or sidebar "Branch Requests"
   - Filter by "Pending"
   - Click "View Details" on request
   - Review all information

3. **Approve Request**
   - Click "Approve Request"
   - Confirm action
   - Verify branch created
   - Verify notification sent

4. **Reject Request** (Alternative)
   - Click "Reject Request"
   - Enter detailed rejection reason
   - Submit rejection
   - Verify notification sent

5. **Login as Business Admin** (Verify Notification)
   - Check for approval/rejection notification
   - If approved: View branch on map
   - If rejected: Read reason, submit corrected request

## Audit Trail

All branch requests maintain complete audit trail:
- Original requester and timestamp
- All request details preserved
- Reviewer and review timestamp
- Approval/rejection decision
- Rejection reason (if applicable)

This ensures accountability and provides historical record of all branch creation attempts.
