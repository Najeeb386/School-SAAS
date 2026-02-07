# Grading Criteria Management System - Setup Guide

## Overview
A complete grading criteria management system has been implemented with CRUD operations, form validation, and a responsive UI.

## Files Created

### 1. Model Layer
**File**: `App/Modules/School_Admin/Models/GradingCriteriaModel.php`
- Handles database operations for grading criteria
- Methods:
  - `getGradingCriteriaBySchool()` - Fetch all criteria for a school
  - `getGradingCriteriaById()` - Fetch single criteria
  - `addGradingCriteria()` - Insert new criteria
  - `updateGradingCriteria()` - Update existing criteria
  - `deleteGradingCriteria()` - Delete criteria
  - `gradeNameExists()` - Validate unique grade names
  - `percentageRangeExists()` - Prevent overlapping ranges

### 2. Controller Layer
**File**: `App/Modules/School_Admin/Controllers/GradingCriteriaController.php`
- Business logic and validation
- Methods:
  - `getGradingCriteria()` - Get all criteria with output formatting
  - `getGradingCriteriaById()` - Get single criteria
  - `addGradingCriteria()` - Validate and add new criteria
  - `updateGradingCriteria()` - Validate and update criteria
  - `deleteGradingCriteria()` - Delete criteria with validation

**Validations Implemented**:
- Required field validation (grade_name, min_percentage, max_percentage)
- Percentage range validation (0-100, min <= max)
- Duplicate grade name prevention
- Overlapping percentage range prevention
- Comprehensive error messages

### 3. API Endpoint
**File**: `App/Modules/School_Admin/Views/examination/manage_grading_criteria.php`
- RESTful endpoint for CRUD operations
- Supported Actions:
  - `action=get` - Retrieve all grading criteria
  - `action=add` - Create new grading criteria
  - `action=update` - Update existing criteria
  - `action=delete` - Remove criteria
- Returns JSON responses with clear success/error messages
- Implements proper HTTP response codes (201 for creation, 200 for success, 400 for error)

### 4. View/UI
**File**: `App/Modules/School_Admin/Views/examination/grading_criteria.php`
- Responsive table display of all grading criteria
- Features:
  - Sortable table with all criteria attributes
  - Color-coded badges for pass/fail status
  - Visual indicators for grading system type
  - Status indicators (Active/Inactive)

**Modal Form Features**:
- Add/Edit modal with toggle behavior
- Form inputs for all database fields:
  - Grade Name (text input)
  - Min/Max Percentage (number inputs with validation)
  - GPA (optional number input)
  - Remarks (optional text input)
  - Passing Grade (checkbox toggle)
  - Grading System (dropdown: Percentage, GPA, Both)
  - Active Status (checkbox toggle)
- Bootstrap styling with large form controls
- Black text labels with border highlighting
- Client-side and server-side validation

**JavaScript Functionality**:
- `loadGradingCriteria()` - Fetch and display criteria
- `openGradingCriteriaModal()` - Open empty form for new entry
- `editGradingCriteria(id)` - Load existing data for editing
- `saveGradingCriteria()` - Validate and submit form
- `deleteGradingCriteria(id)` - Delete with confirmation

### 5. Database Migration
**File**: `SQL/create_grading_criteria_table.sql`
- Creates `school_grading_criteria` table
- Column Details:
  - `id` - Primary key (INT, auto-increment)
  - `school_id` - Foreign key to schools table
  - `grade_name` - Grade symbol (A+, A, B, C, D, F)
  - `min_percentage` - Minimum percentage (DECIMAL 5,2)
  - `max_percentage` - Maximum percentage (DECIMAL 5,2)
  - `gpa` - GPA value (DECIMAL 3,2, nullable)
  - `remarks` - Grade remarks/description (VARCHAR 100, nullable)
  - `is_pass` - Pass/Fail indicator (TINYINT 1, default 1)
  - `grading_system` - System type (ENUM: percentage, gpa, both)
  - `status` - Active/Inactive (TINYINT 1, default 1)
  - Timestamps for audit trail

**Indexes**:
- Unique constraint on (school_id, grade_name)
- Unique constraint on (school_id, min_percentage, max_percentage)
- Index on school_id, grading_system, is_pass
- Foreign key constraint on school_id

## Installation Steps

### Step 1: Create Database Table
Execute the SQL migration file:
```bash
# Via MySQL client:
mysql -u username -p database_name < SQL/create_grading_criteria_table.sql

# Or manually copy-paste the SQL content in your database management tool
```

### Step 2: Verify Files
All files have been validated with no syntax errors:
- ✓ GradingCriteriaModel.php - No errors
- ✓ GradingCriteriaController.php - No errors
- ✓ manage_grading_criteria.php - No errors
- ✓ grading_criteria.php - No errors

### Step 3: Access the Page
Navigate to: `/App/Modules/School_Admin/Views/examination/grading_criteria.php`

The page requires School Admin authentication (already configured).

## Usage Guide

### Adding a New Grade
1. Click **"Add New Grade"** button
2. Fill in all required fields:
   - Grade Name (e.g., A+, A, B)
   - Minimum and Maximum Percentage
   - Grading System (Percentage, GPA, or Both)
3. Optionally fill:
   - GPA value
   - Remarks (Excellent, Good, Pass, Fail, etc.)
4. Check/uncheck pass status
5. Click **"Save Grade"**

### Editing a Grade
1. Click **Edit** (pencil icon) button in the Actions column
2. Modal loads with existing data
3. Modify fields as needed
4. Click **"Save Grade"** to update

### Deleting a Grade
1. Click **Delete** (trash icon) button
2. Confirm the deletion
3. Grade is removed from the system

### Grade Display Features
- **Grade Name**: Bold text for visibility
- **Percentage Range**: Min% to Max% display
- **GPA**: Shows GPA value or "-" if not set
- **Remarks**: Shows remarks or "-" if not set
- **Pass Status Badge**:
  - Green "Passing" for passing grades
  - Red "Failing" for failing grades
- **Grading System Badge**:
  - Blue "Percentage" for percentage-based
  - Gray "Gpa" for GPA-based
  - Yellow "Both" for combined systems
- **Status Badge**: Green "Active" or Gray "Inactive"

## Form Validations

### Client-Side (JavaScript)
- Required field checking
- Percentage range validation (0-100)
- HTML5 input validation

### Server-Side (PHP)
- Null/empty field validation
- Percentage range logic validation (min <= max, 0-100)
- Duplicate grade name detection (unique per school)
- Overlapping percentage range detection
- Comprehensive error messages returned as JSON

## Database Constraints

The system enforces:
1. **Unique Grade Names**: A school cannot have duplicate grade names
   ```sql
   UNIQUE KEY uniq_school_grade (school_id, grade_name)
   ```

2. **Non-Overlapping Ranges**: Percentage ranges cannot overlap within a school
   ```sql
   UNIQUE KEY uniq_school_range (school_id, min_percentage, max_percentage)
   ```

3. **School Data Isolation**: Each school can only see/manage its own grading criteria
   ```sql
   FOREIGN KEY (school_id) REFERENCES schools (id) ON DELETE CASCADE
   ```

## API Reference

### Get All Grading Criteria
```
GET /manage_grading_criteria.php?action=get
Response (200 OK):
{
    "success": true,
    "data": [
        {
            "id": 1,
            "school_id": 1,
            "grade_name": "A+",
            "min_percentage": "90.00",
            "max_percentage": "100.00",
            "gpa": "4.00",
            "remarks": "Excellent",
            "is_pass": 1,
            "grading_system": "both",
            "status": 1,
            "created_at": "2026-02-07 10:00:00",
            "updated_at": "2026-02-07 10:00:00"
        }
    ]
}
```

### Add New Grading Criteria
```
POST /manage_grading_criteria.php?action=add
Content-Type: application/json

{
    "grade_name": "A+",
    "min_percentage": 90,
    "max_percentage": 100,
    "gpa": 4.0,
    "remarks": "Excellent",
    "is_pass": 1,
    "grading_system": "both",
    "status": 1
}

Response (201 Created or 400 Bad Request):
{
    "success": true,
    "message": "Grading criteria 'A+' added successfully"
}
```

### Update Grading Criteria
```
POST /manage_grading_criteria.php?action=update
Content-Type: application/json

{
    "id": 1,
    "grade_name": "A+",
    "min_percentage": 90,
    "max_percentage": 100,
    "gpa": 4.0,
    "remarks": "Excellent",
    "is_pass": 1,
    "grading_system": "both",
    "status": 1
}

Response (200 OK or 400 Bad Request):
{
    "success": true,
    "message": "Grading criteria 'A+' updated successfully"
}
```

### Delete Grading Criteria
```
POST /manage_grading_criteria.php?action=delete
Content-Type: application/json

{
    "id": 1
}

Response (200 OK or 400 Bad Request):
{
    "success": true,
    "message": "Grading criteria 'A+' deleted successfully"
}
```

## Features

✅ **Complete CRUD Operations**
- Create, Read, Update, Delete grading criteria
- Automatic timestamps (created_at, updated_at)

✅ **Data Validation**
- Unique grade names per school
- Non-overlapping percentage ranges
- Range validation (0-100%)
- Required field validation

✅ **User-Friendly Interface**
- Responsive table with sorting
- Modal-based form for easy data entry
- Color-coded status indicators
- Clear success/error messages

✅ **Security**
- School isolation (each school manages only their criteria)
- Authentication required (School Admin)
- SQL injection prevention (prepared statements)
- XSS protection (HTML escaping)

✅ **Accessibility**
- Bootstrap form controls
- Large input fields (form-control-lg)
- Clear labels and descriptions
- ARIA labels for screen readers

## Example Grading Scale

You can use this as a template when setting up your first grading criteria:

| Grade | Min % | Max % | GPA | Remarks | Pass |
|-------|-------|-------|-----|---------|------|
| A+ | 90 | 100 | 4.0 | Excellent | ✓ |
| A | 85 | 89.99 | 3.7 | Very Good | ✓ |
| B | 75 | 84.99 | 3.0 | Good | ✓ |
| C | 65 | 74.99 | 2.0 | Pass | ✓ |
| D | 50 | 64.99 | 1.0 | Pass | ✓ |
| F | 0 | 49.99 | 0.0 | Fail | ✗ |

## Troubleshooting

### Module Not Found Error
Ensure the namespace paths are correct:
- Model: `App\Modules\School_Admin\Models\GradingCriteriaModel`
- Controller: `App\Modules\School_Admin\Controllers\GradingCriteriaController`

### Database Connection Error
- Verify `Database::getInstance()` is properly configured
- Check that database autoloader includes the new model and controller

### 401 Unauthorized Error
- Ensure you're logged in as School Admin
- Check that `$_SESSION['school_id']` is set

### CORS/AJAX Issues
- Verify `manage_grading_criteria.php` is in the correct directory
- Check browser console for detailed error messages

## Future Enhancements

Potential improvements:
1. Import/Export grading criteria templates
2. Bulk grading system setup wizard
3. Grade distribution analytics
4. Percentage to letter grade conversion calculator
5. Append custom fields to grades

## Support

All files follow the existing School SaaS architecture patterns:
- Namespace usage matches other modules
- Database patterns follow existing models
- API endpoints use consistent JSON response format
- UI styling uses Bootstrap consistently with other views

For additional help, refer to:
- `QUICK_SETUP_GUIDE.md` for general setup
- `AUTHENTICATION_IMPLEMENTATION_GUIDE.md` for auth details
- `DATABASE_DOCUMENTATION.md` for database structure
