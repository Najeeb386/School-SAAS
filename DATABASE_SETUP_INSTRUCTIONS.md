# Database Setup - Execution Instructions

## Quick Database Setup (3 Steps)

### Step 1: Open phpMyAdmin
1. Go to: `http://localhost/phpmyadmin`
2. Login with your credentials (usually root / empty password)
3. Select your database (e.g., `School-SAAS`)

### Step 2: Navigate to SQL Tab
1. Click on the "SQL" tab at the top
2. You should see a large text area for entering SQL queries

### Step 3: Copy and Paste Schema
1. Open the file: `SQL/staff_attendance_tables.sql`
2. Copy ALL content
3. Paste into the SQL text area in phpMyAdmin
4. Click the "Go" or "Execute" button

---

## Detailed Step-by-Step

### Using phpMyAdmin (Easiest)

**Step 1: Login to phpMyAdmin**
```
URL: http://localhost/phpmyadmin
Username: root
Password: (leave blank or your password)
```

**Step 2: Select Your Database**
- Find your database in the left sidebar (e.g., "School-SAAS")
- Click to select it

**Step 3: Go to SQL Tab**
- Click the "SQL" tab in the main panel

**Step 4: Copy the SQL File**
- Open `SQL/staff_attendance_tables.sql` in any text editor
- Select all content (Ctrl+A)
- Copy (Ctrl+C)

**Step 5: Paste and Execute**
- In phpMyAdmin SQL textarea, paste the content (Ctrl+V)
- Click the "Go" button (bottom right)
- Wait for the message: "X queries executed successfully"

---

### Alternative: Using MySQL Command Line

**Step 1: Open Command Prompt/Terminal**
```
Windows: Start → cmd
Mac/Linux: Terminal
```

**Step 2: Navigate to XAMPP Directory**
```bash
cd C:\xampp\mysql\bin
```

**Step 3: Execute SQL File**
```bash
mysql -u root -p your_database_name < "D:\Softwares\Xampp\htdocs\School-SAAS\SQL\staff_attendance_tables.sql"
```

When prompted for password, press Enter (default XAMPP has no password)

**Step 4: Verify**
```bash
mysql -u root -p your_database_name
SHOW TABLES;
```

---

## Verification After Execution

### Verify Tables Created

**In phpMyAdmin**:
1. Click on your database name in left sidebar
2. You should see these tables listed:
   - `attendance_settings`
   - `leave_types`
   - `school_staff`
   - `staff_attendance`
   - `staff_attendance_summary`

**Or run this SQL**:
```sql
SHOW TABLES LIKE '%attendance%';
SHOW TABLES LIKE '%leave%';
SHOW TABLES LIKE '%staff%';
```

**Expected Output**:
```
attendance_settings
leave_types
school_staff
staff_attendance
staff_attendance_summary
```

### Verify Sample Data Loaded

**Check Staff Table**:
```sql
SELECT COUNT(*) as total_staff FROM school_staff;
-- Expected: 8
```

**Check Attendance Records**:
```sql
SELECT COUNT(*) as total_records FROM staff_attendance;
-- Expected: 15 or more
```

**Check Leave Types**:
```sql
SELECT COUNT(*) as total_leave_types FROM leave_types;
-- Expected: 5
```

**Check Settings**:
```sql
SELECT COUNT(*) as total_settings FROM attendance_settings;
-- Expected: 1
```

### View Sample Data

**See All Staff**:
```sql
SELECT * FROM school_staff;
```

**See All Attendance Records**:
```sql
SELECT 
    ss.name,
    sa.attendance_date,
    sa.status
FROM staff_attendance sa
JOIN school_staff ss ON sa.staff_id = ss.id
ORDER BY sa.attendance_date, ss.name;
```

**See Leave Types**:
```sql
SELECT * FROM leave_types;
```

**See Attendance Settings**:
```sql
SELECT * FROM attendance_settings;
```

---

## Test Queries to Run After Setup

### Test 1: Get All Staff for February 2026

```sql
SELECT 
    ss.id,
    ss.employee_id,
    ss.name,
    ss.designation,
    ss.department
FROM school_staff ss
WHERE ss.school_id = 1
ORDER BY ss.name;
```

**Expected**: 8 rows with staff data

---

### Test 2: Get February 2026 Attendance

```sql
SELECT 
    ss.name,
    sa.attendance_date,
    sa.status
FROM staff_attendance sa
JOIN school_staff ss ON sa.staff_id = ss.id
WHERE YEAR(sa.attendance_date) = 2026
    AND MONTH(sa.attendance_date) = 2
ORDER BY ss.name, sa.attendance_date;
```

**Expected**: 15+ rows with attendance records

---

### Test 3: Get Department-wise Staff Count

```sql
SELECT 
    department,
    COUNT(*) as staff_count
FROM school_staff
WHERE school_id = 1
GROUP BY department
ORDER BY department;
```

**Expected Output**:
```
Admin       1
Finance     1
Library     1
Student Support  1
Support     1
Teaching    2
```

---

### Test 4: Test Insert New Attendance

```sql
INSERT INTO staff_attendance 
    (staff_id, school_id, attendance_date, status, marked_by, marked_at)
VALUES 
    (1, 1, '2026-02-10', 'present', 5, NOW());
```

**Expected**: Query executed successfully (no errors)

**Verify**:
```sql
SELECT * FROM staff_attendance 
WHERE staff_id = 1 AND attendance_date = '2026-02-10';
```

**Expected**: 1 row with status = 'present'

---

### Test 5: Test Update Attendance (Duplicate Key)

```sql
-- Update existing record
INSERT INTO staff_attendance 
    (staff_id, school_id, attendance_date, status, marked_by, marked_at)
VALUES 
    (1, 1, '2026-02-10', 'absent', 5, NOW())
ON DUPLICATE KEY UPDATE
    status = 'absent',
    updated_at = NOW();
```

**Expected**: Query executed (either inserted new or updated existing)

**Verify**:
```sql
SELECT * FROM staff_attendance 
WHERE staff_id = 1 AND attendance_date = '2026-02-10';
```

**Expected**: 1 row with status = 'absent' (updated)

---

### Test 6: Test Views

```sql
-- Test current month attendance view
SELECT * FROM v_current_month_attendance LIMIT 5;
```

**Expected**: Rows with staff info and attendance data

```sql
-- Test summary report view
SELECT * FROM v_attendance_summary_report LIMIT 5;
```

**Expected**: Rows with summary statistics

---

## Troubleshooting

### Error: "Table already exists"
**Cause**: Tables were created previously
**Solution**: Use `DROP TABLE IF EXISTS` before creating, or delete tables manually first

```sql
-- Drop all tables (careful!)
DROP TABLE IF EXISTS staff_attendance;
DROP TABLE IF EXISTS staff_attendance_summary;
DROP TABLE IF EXISTS school_staff;
DROP TABLE IF EXISTS leave_types;
DROP TABLE IF EXISTS attendance_settings;
```

Then re-run the schema file.

### Error: "Unknown column in foreign key"
**Cause**: `schools` table doesn't exist
**Solution**: The SQL file has commented-out foreign keys. This is OK for testing.

To enable foreign keys, first create a `schools` table or uncomment the FK constraints.

### Error: "Syntax error near line X"
**Cause**: Incomplete copy-paste of SQL file
**Solution**: Copy the entire file again, making sure you get everything from line 1 to the end.

### Error: "Access denied for user 'root'@'localhost'"
**Cause**: Wrong password or username
**Solution**: 
- In XAMPP, MySQL usually has no password
- Try leaving password blank
- Or check your MySQL user setup

### Tables not showing in phpMyAdmin
**Solution**: 
1. Refresh the page (F5)
2. Click on the database name again
3. Check the "Tables" list in the left sidebar

---

## Quick Reference

### File Locations
- Schema file: `d:\Softwares\Xampp\htdocs\School-SAAS\SQL\staff_attendance_tables.sql`
- Frontend: `d:\Softwares\Xampp\htdocs\School-SAAS\App\Modules\School_Admin\Views\attendence\staff_attendence.php`
- Documentation: `d:\Softwares\Xampp\htdocs\School-SAAS\DATABASE_DOCUMENTATION.md`

### Important Notes
- Default `school_id` in sample data is `1`
- All dates are for February 2026 (for testing)
- Sample staff IDs: EMP001 through EMP008
- Leave types: CL, SL, AL, ML, PL

### Next Steps After Database Setup
1. ✅ Execute SQL file (THIS DOCUMENT)
2. ⏳ Verify tables created and data loaded
3. ⏳ Create backend API endpoints (see INTEGRATION_GUIDE.md)
4. ⏳ Test frontend with real database
5. ⏳ Deploy to production

---

## Still Have Questions?

📍 **For Schema Details**: See `DATABASE_DOCUMENTATION.md`
📍 **For API Integration**: See `ATTENDANCE_SYSTEM_INTEGRATION_GUIDE.md`
📍 **For Quick Setup**: See `QUICK_SETUP_GUIDE.md`

---

**All set! Your database is ready to go.** 🎉
