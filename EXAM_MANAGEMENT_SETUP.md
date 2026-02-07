# Exam Management System - Setup Guide

## Overview
A complete exam management system has been implemented with CRUD operations, filtering capabilities, and a responsive UI for creating and managing school exams.

## Features

✅ **Complete CRUD Operations**
- Create new exams
- Read/display exams in table
- Update existing exams
- Delete exams with confirmation

✅ **Advanced Filtering**
- Filter by exam type (Monthly Test, Mid Term, Final Term, Quarterly, Annual)
- Filter by session
- Filter by date range (from date to date)
- Reset filters to view all exams

✅ **Professional Table Display**
- Responsive table with all exam details
- Color-coded exam type badges
- Active/Inactive status indicators
- Quick edit and delete actions
- Sortable by date (newest first)

✅ **Modal Form for Add/Edit**
- Exam Name (unique validation)
- Exam Type dropdown (5 types)
- Session selection
- Exam Date picker
- Optional start and end times
- Optional description text
- Status toggle (active/inactive)

✅ **Data Validation**
- Required field validation
- Unique exam name per school
- Date validation
- Time format validation
- Comprehensive error messages

✅ **Security**
- School data isolation
- Authentication required
- SQL injection prevention
- XSS protection

## Files Created

### 1. Model Layer
**File**: `App/Modules/School_Admin/Models/ExamModel.php`
- Handles all database operations
- Methods:
  - `getExamsBySchool()` - Get all exams for a school
  - `getExamsFiltered()` - Get exams with filters applied
  - `getExamById()` - Get single exam
  - `addExam()` - Insert new exam
  - `updateExam()` - Update exam
  - `deleteExam()` - Delete exam
  - `examNameExists()` - Check for duplicate names

### 2. Controller Layer
**File**: `App/Modules/School_Admin/Controllers/ExamController.php`
- Business logic and validation
- Methods:
  - `getExams()` - Get all exams
  - `getFilteredExams()` - Apply filters
  - `getSessions()` - Get sessions for dropdown
  - `addExam()` - Validate and add exam
  - `updateExam()` - Validate and update exam
  - `deleteExam()` - Delete with validation

**Validations**:
- Required field checking
- Exam type validation
- Date validation
- Duplicate name prevention
- Time format validation

### 3. API Endpoint
**File**: `App/Modules/School_Admin/Views/examination/generate_exam/manage_exams.php`
- RESTful API for CRUD operations
- Supported actions:
  - `action=get` - Retrieve all exams
  - `action=filter` - Get filtered exams
  - `action=sessions` - Get sessions for dropdown
  - `action=add` - Create new exam
  - `action=update` - Update exam
  - `action=delete` - Remove exam
- JSON responses with proper HTTP codes

### 4. View/UI
**File**: `App/Modules/School_Admin/Views/examination/generate_exam/generate_exam.php`

**Features**:
- Header with "Create New Exam" button
- Four-column filter section:
  - Exam Type dropdown (5 options)
  - Session dropdown (dynamic)
  - From Date picker
  - To Date picker
- Reset filters button

- Responsive exam table with:
  - Exam Name
  - Color-coded Type badges
  - Session name
  - Exam Date (formatted)
  - Time range display
  - Status badge
  - Edit/Delete action buttons

- Modal form for create/edit:
  - All fields with proper validation
  - Better text visibility (black text, 15px font)
  - Bordered input fields
  - Bootstrap styling

### 5. Database Migration
**File**: `SQL/create_exams_table.sql`
- Creates `school_exams` table
- Columns:
  - `id` - Primary key
  - `school_id` - School reference
  - `session_id` - Academic session reference
  - `exam_type` - Enum (monthly_test, mid_term, final_term, quarterly, annual)
  - `exam_name` - Exam name/title
  - `exam_date` - Exam date
  - `start_time` - Optional start time
  - `end_time` - Optional end time
  - `description` - Optional notes
  - `status` - Active/Inactive flag
  - `created_by` - Creator user ID
  - Timestamps (created_at, updated_at)

**Indexes**:
- Unique on (school_id, exam_name)
- Index on school_id, session_id, exam_type, exam_date, status

**Foreign Keys**:
- References schools table (cascade delete)
- References school_sessions table (cascade delete)

## Installation Steps

### Step 1: Create Database Table
Execute the SQL migration:
```bash
# Run in MySQL:
mysql -u username -p database_name < SQL/create_exams_table.sql

# Or use database management tool to run SQL/create_exams_table.sql
```

### Step 2: Verify File Locations
All files are in correct locations:
- ✓ Model: `App/Modules/School_Admin/Models/ExamModel.php`
- ✓ Controller: `App/Modules/School_Admin/Controllers/ExamController.php`
- ✓ API: `generate_exam/manage_exams.php`
- ✓ View: `generate_exam/generate_exam.php`
- ✓ Database: `SQL/create_exams_table.sql`

### Step 3: Verify Configuration
- Database connection is working
- SessionModel is accessible (used in ExamController)
- Database class has `connect()` method available

### Step 4: Test the Page
Navigate to: `/App/Modules/School_Admin/Views/examination/generate_exam/generate_exam.php`

## Usage Guide

### Creating a New Exam
1. Click **"Create New Exam"** button (top right)
2. Modal opens with empty form
3. Fill in required fields:
   - **Exam Name**: e.g., "Mathematics Mid Term 2026"
   - **Exam Type**: Select from dropdown
   - **Session**: Select academic session
   - **Exam Date**: Pick date from calendar
4. Optionally fill:
   - Start Time
   - End Time
   - Description
5. Check "Active" if you want exam to be active
6. Click **"Save Exam"**
7. Page reloads and shows success message

### Editing an Exam
1. Click **Edit** button (pencil icon) in table row
2. Modal opens with existing data
3. Modify any fields
4. Click **"Save Exam"**
5. Page reloads with updated data

### Deleting an Exam
1. Click **Delete** button (trash icon)
2. Confirm deletion in dialog
3. Exam is removed from database

### Filtering Exams
1. Use filter section at top of table
2. Select exam type (or leave blank for all)
3. Select session (or leave blank for all)
4. Pick start date (or leave blank)
5. Pick end date (or leave blank)
6. Filters apply automatically
7. Click **"Reset Filters"** to see all exams again

## Exam Types

| Type | Description |
|------|-------------|
| Monthly Test | Regular monthly assessment |
| Mid Term | Mid-term examination |
| Final Term | Final term examination |
| Quarterly | Quarterly assessment |
| Annual | Annual/yearly examination |

## Exam Display

Each exam shows:
- **Name**: Bold text for visibility
- **Type**: Color-coded badge
  - Blue: Monthly Test
  - Yellow: Mid Term
  - Red: Final Term
  - Cyan: Quarterly
  - Green: Annual
- **Session**: Name of academic session
- **Date**: Formatted date (e.g., Jan 15, 2026)
- **Time**: Shows time range or individual times or "-" if not set
- **Status**: Green "Active" or Gray "Inactive"
- **Actions**: Edit and Delete buttons

## API Reference

### Get All Exams
```
GET /generate_exam/manage_exams.php?action=get
Response (200):
{
    "success": true,
    "data": [
        {
            "id": 1,
            "school_id": 1,
            "session_id": 1,
            "exam_type": "mid_term",
            "exam_name": "Mathematics Mid Term",
            "exam_date": "2026-02-15",
            "start_time": "09:00:00",
            "end_time": "11:00:00",
            "description": "Full syllabus exam",
            "status": 1,
            "created_by": 5,
            "created_at": "2026-02-07 10:00:00",
            "updated_at": "2026-02-07 10:00:00",
            "session_name": "2025-2026"
        }
    ]
}
```

### Filter Exams
```
POST /generate_exam/manage_exams.php?action=filter
Content-Type: application/json

{
    "exam_type": "mid_term",
    "session_id": 1,
    "start_date": "2026-01-01",
    "end_date": "2026-03-31"
}

Response (200):
{
    "success": true,
    "data": [...]
}
```

### Get Sessions
```
GET /generate_exam/manage_exams.php?action=sessions
Response (200):
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "2025-2026",
            ...
        }
    ]
}
```

### Create Exam
```
POST /generate_exam/manage_exams.php?action=add
Content-Type: application/json

{
    "exam_name": "English Final Term",
    "exam_type": "final_term",
    "session_id": 1,
    "exam_date": "2026-03-15",
    "start_time": "14:00",
    "end_time": "16:00",
    "description": "Comprehensive English exam",
    "status": 1
}

Response (201):
{
    "success": true,
    "message": "Exam 'English Final Term' created successfully"
}
```

### Update Exam
```
POST /generate_exam/manage_exams.php?action=update
Content-Type: application/json

{
    "id": 1,
    "exam_name": "English Final Term (Updated)",
    ...
}

Response (200):
{
    "success": true,
    "message": "Exam 'English Final Term (Updated)' updated successfully"
}
```

### Delete Exam
```
POST /generate_exam/manage_exams.php?action=delete
Content-Type: application/json

{
    "id": 1
}

Response (200):
{
    "success": true,
    "message": "Exam 'English Final Term' deleted successfully"
}
```

## Error Responses

```json
{
    "success": false,
    "message": "Exam name, session, type, and date are required"
}
```

Common errors:
- "Exam name, session, type, and date are required" - Missing required fields
- "Exam 'Name' already exists" - Duplicate exam name
- "Invalid exam type selected" - Invalid exam type
- "Invalid exam date" - Date format error
- "Exam not found" - ID doesn't exist
- "Unauthorized" - Not logged in

## File Validation

✅ All files validated with zero syntax errors:
- ExamModel.php - No errors
- ExamController.php - No errors
- manage_exams.php - No errors
- generate_exam.php - No errors

## Browser Compatibility

- Chrome/Edge: Fully supported
- Firefox: Fully supported
- Safari: Fully supported
- Mobile browsers: Responsive design supported

## Security Features

1. **Authentication**: School Admin role required
2. **Data Isolation**: Each school only sees their exams
3. **SQL Injection Prevention**: Prepared statements with PDO
4. **XSS Protection**: HTML escaping in JavaScript output
5. **Input Validation**: Server-side validation for all inputs
6. **Session Security**: Session ID validation

## Performance Notes

- Exams load instantly from database
- Filtering is real-time (POST requests)
- Sessions loaded once on page load
- Table updates without full refresh on delete
- Pagination ready (currently shows all)

## Future Enhancements

Potential improvements:
1. Add exam syllabus/topics mapping
2. Add question paper uploads
3. Add result entry sheet
4. Add exam schedule PDF export
5. Add email notifications
6. Add duplicate exam creation
7. Add bulk exam import
8. Add exam statistics dashboard

## Troubleshooting

### Table Shows "Loading exams..."
- Check browser console for errors (F12)
- Verify database table exists
- Check manage_exams.php API response

### Modal Won't Open
- Clear browser cache
- Hard refresh page (Ctrl+Shift+R)
- Check console for JavaScript errors

### Can't Filter Exams
- Sessions dropdown might be empty
- Check that sessions exist in database
- Try resetting filters first

### "Unauthorized" Error
- Ensure you're logged in as School Admin
- Check that $\_SESSION['school_id'] is set
- Verify auth middleware is working

## Database Notes

- Records are ordered by exam_date DESC (newest first)
- Default status is 1 (Active)
- Exam names are unique per school
- All timestamps in UTC
- Soft delete not implemented (consider adding)

## Configuration

No additional configuration needed. System uses:
- Existing Database::connect() method
- Existing session management
- Bootstrap 5 framework
- FontAwesome 6 icons

All integrations are automatic and follow existing patterns in the codebase.
