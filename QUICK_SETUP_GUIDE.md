# Staff Attendance System - Quick Setup Guide

## 📋 What You Have

### ✅ Frontend (Complete)
- **File**: `App/Modules/School_Admin/Views/attendence/staff_attendence.php`
- **Features**:
  - Filter section (Month, Year, Department)
  - Calendar table with staff on left, dates as columns
  - Attendance marking with color-coded badges (P/A/L/HD)
  - Sunday highlighting in red
  - Full month navigation (Prev/Today/Next/Dropdown)
  - Modal for bulk attendance marking
  - Sample data with 8 staff members

### ✅ Database Schema (Complete)
- **File**: `SQL/staff_attendance_tables.sql`
- **Tables**: 5 tables + 2 views + 1 trigger + 1 stored procedure
- **Ready to**: Execute in MySQL to create all tables

### ✅ Documentation (Complete)
- **DATABASE_DOCUMENTATION.md**: Full schema reference
- **ATTENDANCE_SYSTEM_INTEGRATION_GUIDE.md**: Backend integration guide

---

## 🚀 Quick Setup (5 Steps)

### Step 1: Create Database Tables
```bash
cd d:\Softwares\Xampp\htdocs\School-SAAS\SQL

# Import the SQL file into your database
mysql -u root -p your_database < staff_attendance_tables.sql
```

Or in phpMyAdmin:
1. Go to `SQL/staff_attendance_tables.sql`
2. Copy all content
3. Paste in phpMyAdmin SQL tab
4. Click Execute

### Step 2: Verify Tables Created
In phpMyAdmin or MySQL:
```sql
SHOW TABLES LIKE 'staff_%';
SHOW TABLES LIKE 'leave_%';
SHOW TABLES LIKE 'attendance_%';
```

**Expected Output**:
- school_staff ✓
- staff_attendance ✓
- staff_attendance_summary ✓
- leave_types ✓
- attendance_settings ✓

### Step 3: Verify Sample Data
```sql
SELECT COUNT(*) as total_staff FROM school_staff WHERE school_id = 1;
SELECT COUNT(*) as total_records FROM staff_attendance;
```

**Expected**:
- total_staff: 8
- total_records: 15+ (sample records for February 2026)

### Step 4: Test Frontend
1. Open: `http://localhost/School-SAAS/App/Modules/School_Admin/Views/attendence/staff_attendence.php`
2. You should see:
   - Filter section at top
   - Calendar table with 8 staff members
   - February 2026 dates
   - Color-coded attendance badges
   - Sundays highlighted in red

### Step 5: Next - Create Backend (Optional but Recommended)

Create file: `App/Modules/School_Admin/Controllers/AttendanceController.php`

Copy code from: `ATTENDANCE_SYSTEM_INTEGRATION_GUIDE.md` → Part 3

This will enable:
- Real data from database instead of sample data
- Saving attendance to database
- Department filtering
- Monthly summaries

---

## 🔄 Database Overview

```
school_staff (Master)
├── 8 sample staff (EMP001-EMP008)
├── 5 departments: Teaching, Library, Admin, Finance, Support
└── All status: active

staff_attendance (Daily Records)
├── 15 sample records (February 2026)
├── Status: P/A/L/HD/Not Marked
└── Ready for more data

staff_attendance_summary (Monthly)
├── Empty (will populate after backend integration)
└── Stores monthly statistics

leave_types (Configuration)
├── 5 leave types: CL, SL, AL, ML, PL
└── Max days per type configured

attendance_settings (School Config)
├── Working days: 5 per week
├── Working hours: 8 per day
├── Min halfday hours: 4
└── Weekend: Saturday, Sunday
```

---

## 📊 Filter Features

**Month Dropdown**:
- 12 options (January to December)
- Values: 0-11 (matching JavaScript months)
- Default: Current month

**Year Dropdown**:
- Range: Current year ±2 years
- Dynamically generated
- Default: Current year

**Department Dropdown**:
- All Departments
- Teaching
- Library
- Admin
- Finance
- Support

**Apply Button**: Updates calendar for selected month/year/department

**Reset Button**: Returns to today and clears filters

---

## 🎯 Current State

| Component | Status | Notes |
|-----------|--------|-------|
| **Database** | ✅ Ready | Execute SQL file |
| **UI/HTML** | ✅ Complete | Fully styled with Bootstrap |
| **JavaScript** | ✅ Complete | Calendar generation, event handlers |
| **Sample Data** | ✅ Ready | 8 staff, 15 attendance records |
| **Frontend Display** | ✅ Working | Hardcoded sample data |
| **Backend API** | ⏳ Pending | Code template provided |
| **Database Integration** | ⏳ Pending | Need AttendanceController |
| **Save to Database** | ⏳ Pending | Need POST endpoint |

---

## 🔧 Customization

### Change Sample Data
Edit `staff_attendence.php` lines 486-495:
```javascript
const allStaff = [
    {id: 1, employee_id: 'EMP001', name: 'John Smith', designation: 'Teacher', department: 'Teaching'},
    // Add/modify staff here
];
```

### Change Filter Departments
Edit `staff_attendence.php` lines 613-618:
```html
<select id="filterDept">
    <option value="">All Departments</option>
    <option value="Teaching">Teaching</option>
    <!-- Add more departments here -->
</select>
```

### Change Colors
Edit `staff_attendence.php` CSS section (top of file):
- `.badge-success`: Present (green) → P
- `.badge-danger`: Absent (red) → A
- `.badge-warning`: Leave (yellow) → L
- `.badge-info`: Half Day (blue) → HD

### Change Weekend Highlighting
Edit `generateCalendarView()` function around line 1100:
```javascript
// Look for: if (dayOfWeek === 0) // Sunday
// Change 0 to different day (0=Sunday, 1=Monday, etc.)
```

---

## 🧪 Testing Checklist

- [ ] Database tables created (5 tables)
- [ ] Sample data loaded (8 staff)
- [ ] Frontend loads without errors
- [ ] Calendar displays February 2026
- [ ] Filter dropdowns populate correctly
- [ ] Sundays highlighted in red
- [ ] Attendance badges show sample data
- [ ] Clicking badges cycles through P/A/L
- [ ] Filter buttons are clickable
- [ ] Month navigation buttons work
- [ ] Today button returns to current date

---

## 🐛 Common Issues & Fixes

### "Table doesn't exist"
```sql
-- Check if tables exist
SHOW TABLES;

-- If not, run SQL file again
mysql -u root -p database_name < staff_attendance_tables.sql
```

### Dates showing wrong month
- Check: Year dropdown is set correctly
- Check: Month dropdown has correct month selected

### Attendance badges not appearing
- Check: Browser console for JavaScript errors (F12)
- Check: allStaff array has data
- Check: generateCalendarView() is being called

### Filter not working
- Check: Apply Filter button click handler
- Check: currentCalendarDate is being updated
- Check: loadMonthlyCalendar() is called after filter

### Sunday highlighting missing
- Check: dayOfWeek === 0 check in generateCalendarView()
- Check: CSS class 'sunday-cell' has red background

---

## 📝 Files Reference

| File | Purpose | Status |
|------|---------|--------|
| staff_attendence.php | Main UI | ✅ Complete |
| SQL/staff_attendance_tables.sql | Database | ✅ Complete |
| DATABASE_DOCUMENTATION.md | Schema docs | ✅ Complete |
| ATTENDANCE_SYSTEM_INTEGRATION_GUIDE.md | Backend guide | ✅ Complete |
| AttendanceController.php | Backend (TODO) | ⏳ To create |

---

## 🎓 Next Steps

### If you want **quick view** (no database):
- You're done! The UI is fully functional with sample data

### If you want **full integration** (with database):

1. **Create backend controller** (5 min)
   - Copy code from `ATTENDANCE_SYSTEM_INTEGRATION_GUIDE.md`
   - Save as `AttendanceController.php`

2. **Replace sample data** (5 min)
   - Replace `allStaff` array with `loadStaffFromDatabase()` call

3. **Add save functionality** (5 min)
   - Update `toggleAttendanceStatus()` to POST to backend

4. **Test end-to-end** (10 min)
   - Load page, filter, mark attendance, verify in database

---

## 💡 Tips

- Use browser DevTools (F12) to debug JavaScript
- Check MySQL error log for database issues
- Use `console.log()` to debug filter values
- Test with small dataset first

---

## 📞 Support

For detailed info see:
- **DATABASE_DOCUMENTATION.md** - All table structures
- **ATTENDANCE_SYSTEM_INTEGRATION_GUIDE.md** - Backend implementation
- **staff_attendence.php** - Frontend code with comments

All files are in: `d:\Softwares\Xampp\htdocs\School-SAAS\`

---

**Ready to go! 🚀**
