# Staff Attendance System - Database Documentation

## Overview
This document describes the database structure for the Staff Attendance Management System. It includes all tables, relationships, and usage patterns.

## Database Tables

### 1. SCHOOL_STAFF Table
**Purpose**: Master table storing information about all staff members

**Columns**:
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Unique staff member identifier |
| school_id | INT (FK) | Reference to schools table |
| employee_id | VARCHAR(50) | Unique employee code (e.g., EMP001) |
| name | VARCHAR(100) | Full name of staff member |
| designation | VARCHAR(100) | Job title (e.g., Teacher, Librarian, Admin) |
| department | VARCHAR(100) | Department name (e.g., Teaching, Admin, Finance) |
| email | VARCHAR(100) | Email address |
| phone | VARCHAR(20) | Contact phone number |
| date_of_joining | DATE | Date of joining |
| status | ENUM | 'active', 'inactive', 'on_leave', 'terminated' |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

**Indexes**:
- Primary: id
- Unique: employee_id
- Index: school_id, status, department

---

### 2. STAFF_ATTENDANCE Table
**Purpose**: Daily attendance records for each staff member

**Columns**:
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Unique attendance record ID |
| staff_id | INT (FK) | Reference to school_staff |
| school_id | INT (FK) | Reference to schools |
| attendance_date | DATE | Date of attendance |
| status | ENUM | 'present', 'absent', 'leave', 'halfday', 'not_marked' |
| remarks | TEXT | Additional notes/remarks |
| marked_by | INT | User ID who marked the attendance |
| marked_at | TIMESTAMP | When the attendance was marked |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

**Unique Constraint**: 
- One attendance record per staff member per date (staff_id + attendance_date)

**Indexes**:
- Primary: id
- Unique: (staff_id, attendance_date)
- Index: school_id, staff_id, attendance_date, status
- Composite: (staff_id, attendance_date), (school_id, attendance_date)

**Sample Data Format**:
```sql
INSERT INTO staff_attendance (staff_id, school_id, attendance_date, status, marked_by, marked_at)
VALUES (1, 1, '2026-02-05', 'present', 5, NOW());
```

---

### 3. STAFF_ATTENDANCE_SUMMARY Table
**Purpose**: Monthly summary for performance tracking and reporting

**Columns**:
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Unique summary record ID |
| staff_id | INT (FK) | Reference to school_staff |
| school_id | INT (FK) | Reference to schools |
| year | INT | Year (e.g., 2026) |
| month | INT | Month (1-12) |
| total_days | INT | Total working days in month |
| present_days | INT | Days marked as present |
| absent_days | INT | Days marked as absent |
| leave_days | INT | Days marked as leave |
| halfday_days | INT | Half days marked |
| not_marked_days | INT | Days not marked |
| attendance_percentage | DECIMAL(5,2) | Calculated percentage (0-100) |
| last_updated | TIMESTAMP | Last calculation time |

**Unique Constraint**: One record per staff per month (staff_id + year + month)

**Usage**: 
- Automatic updates after marking attendance
- Used for monthly reports and analytics
- Helps identify attendance trends

---

### 4. LEAVE_TYPES Table
**Purpose**: Define different types of leaves available in school

**Columns**:
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Unique leave type ID |
| school_id | INT (FK) | Reference to schools |
| name | VARCHAR(100) | Name of leave type (e.g., Casual Leave, Sick Leave) |
| code | VARCHAR(10) | Short code (e.g., CL, SL) |
| max_days | INT | Maximum days allowed per year |
| description | TEXT | Description of leave type |
| status | ENUM | 'active', 'inactive' |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

**Unique Constraint**: (school_id, code)

---

### 5. ATTENDANCE_SETTINGS Table
**Purpose**: School-specific attendance system configuration

**Columns**:
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Unique setting ID |
| school_id | INT (FK) | Reference to schools (unique) |
| working_days_per_week | INT | Default: 5 |
| working_hours_per_day | DECIMAL(4,2) | Default: 8.00 |
| min_working_hours_halfday | DECIMAL(4,2) | Default: 4.00 |
| weekend_days | VARCHAR(50) | e.g., "Saturday,Sunday" |
| fiscal_year_start_month | INT | Default: 4 (April) |
| auto_calculate_summary | BOOLEAN | Auto-calculate monthly summary |
| allow_retroactive_marking | BOOLEAN | Allow marking past dates |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

---

## Data Relationships

```
schools (id)
    ├── school_staff (school_id) ─┬─> staff_attendance (staff_id, school_id)
    │                              ├─> staff_attendance_summary (staff_id, school_id)
    │                              └─> leave_types (school_id)
    └── attendance_settings (school_id)
```

## Common SQL Queries

### Get attendance for specific month/year
```sql
SELECT 
    ss.name,
    ss.employee_id,
    sa.attendance_date,
    sa.status
FROM school_staff ss
JOIN staff_attendance sa ON ss.id = sa.staff_id
WHERE YEAR(sa.attendance_date) = 2026
    AND MONTH(sa.attendance_date) = 2
    AND ss.school_id = 1
ORDER BY ss.name, sa.attendance_date;
```

### Get monthly summary
```sql
SELECT 
    staff_id,
    name,
    designation,
    present_days,
    absent_days,
    leave_days,
    halfday_days,
    attendance_percentage
FROM v_attendance_summary_report
WHERE year = 2026 AND month = 2
ORDER BY name;
```

### Mark attendance
```sql
INSERT INTO staff_attendance 
    (staff_id, school_id, attendance_date, status, marked_by, marked_at)
VALUES (1, 1, '2026-02-05', 'present', 5, NOW())
ON DUPLICATE KEY UPDATE 
    status = 'present',
    marked_by = 5,
    marked_at = NOW(),
    updated_at = NOW();
```

### Get staff with poor attendance
```sql
SELECT 
    name,
    employee_id,
    department,
    attendance_percentage,
    present_days,
    absent_days
FROM v_attendance_summary_report
WHERE attendance_percentage < 70
    AND year = 2026
    AND month = 2
ORDER BY attendance_percentage;
```

### Calculate attendance summary for a month
```sql
REPLACE INTO staff_attendance_summary 
    (staff_id, school_id, year, month, total_days, present_days, absent_days, leave_days, halfday_days, not_marked_days, attendance_percentage)
SELECT 
    ss.id,
    ss.school_id,
    YEAR(sa.attendance_date),
    MONTH(sa.attendance_date),
    COUNT(DISTINCT sa.attendance_date) as total_days,
    SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
    SUM(CASE WHEN sa.status = 'leave' THEN 1 ELSE 0 END) as leave_days,
    SUM(CASE WHEN sa.status = 'halfday' THEN 1 ELSE 0 END) as halfday_days,
    SUM(CASE WHEN sa.status = 'not_marked' THEN 1 ELSE 0 END) as not_marked_days,
    ROUND((SUM(CASE WHEN sa.status IN ('present', 'halfday') THEN 1 ELSE 0 END) / COUNT(DISTINCT sa.attendance_date)) * 100, 2) as percentage
FROM school_staff ss
LEFT JOIN staff_attendance sa ON ss.id = sa.staff_id
WHERE YEAR(sa.attendance_date) = 2026 
    AND MONTH(sa.attendance_date) = 2
    AND ss.school_id = 1
GROUP BY ss.id, ss.school_id, YEAR(sa.attendance_date), MONTH(sa.attendance_date);
```

## Views for Reporting

### v_current_month_attendance
Shows current month's attendance for all staff

### v_attendance_summary_report
Monthly summary with performance rating (Excellent/Good/Average/Poor)

## Performance Optimization

### Indexes Created
1. **staff_id**: For quick staff lookups
2. **attendance_date**: For date range queries
3. **status**: For filtering by attendance status
4. **(staff_id, attendance_date)**: For unique constraint and quick date lookups per staff
5. **(school_id, attendance_date)**: For school-wide attendance reports

### Query Optimization Tips
- Always filter by school_id when querying
- Use indexed columns in WHERE clauses
- Use date range queries instead of LIKE patterns
- Calculate summaries in batches, not real-time

## Data Integrity

### Constraints
1. **Foreign Key**: staff_id must exist in school_staff
2. **Foreign Key**: school_id must exist in schools
3. **Unique**: One attendance per staff per date
4. **Check**: month must be 1-12
5. **Check**: status must be valid enum values

### Data Validation
- attendance_date must be in past (configurable)
- status must be valid enum value
- marked_by must reference valid user
- attendance_percentage must be 0-100

## Maintenance

### Regular Tasks
1. **Daily**: Auto-mark as 'not_marked' for days without attendance
2. **Monthly**: Calculate attendance summary
3. **Quarterly**: Generate reports
4. **Yearly**: Archive old records (optional)

### Backup Schedule
- Daily backup recommended
- Archive old records after 2+ years
- Keep summary data indefinitely for reporting

## Migration Guide

To add this system to existing database:

1. Run `staff_attendance_tables.sql` to create all tables
2. Populate school_staff from existing employee data
3. (Optional) Import historical attendance if available
4. Configure attendance_settings per school
5. Define leave_types for each school
6. Test with sample data
7. Train users on the system
