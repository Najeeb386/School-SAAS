# Staff Attendance Filter Feature - Visual Guide

## 📊 Filter Section Location

The filter section is located **at the top of the staff attendance page**, above the calendar table.

**Path**: `App/Modules/School_Admin/Views/attendence/staff_attendence.php` (Lines 591-630)

---

## 🎨 Visual Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                                       │
│                    Filter by Month & Year                            │
│                                                                       │
│ ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌─────────┬──┐│
│ │ Month        │  │ Year         │  │ Department   │  │Apply   │Res││
│ │ ▼            │  │ ▼            │  │ ▼            │  │Filter  │et ││
│ │              │  │              │  │              │  │        │   ││
│ │February      │  │2026          │  │All Depts     │  └─────────┴──┘│
│ │ ▼ 12 months  │  │ ▼ 5 years    │  │ ▼ 6 options  │                 │
│ │              │  │              │  │              │                 │
│ └──────────────┘  └──────────────┘  └──────────────┘                 │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📋 Filter Components

### 1. Month Selector
```
┌──────────────────────────┐
│ Month                    │
│ ┌──────────────────────┐ │
│ │ February (selected)  │ │
│ │ January              │ │
│ │ March                │ │
│ │ April                │ │
│ │ May                  │ │
│ │ June                 │ │
│ │ July                 │ │
│ │ August               │ │
│ │ September            │ │
│ │ October              │ │
│ │ November             │ │
│ │ December             │ │
│ └──────────────────────┘ │
└──────────────────────────┘
```

**Features**:
- 12 month options (January to December)
- Current month pre-selected
- Dropdown with scrollbar if needed
- Form label: "Month"

---

### 2. Year Selector
```
┌──────────────────────┐
│ Year                 │
│ ┌────────────────┐   │
│ │ 2026 (current) │   │
│ │ 2024           │   │
│ │ 2025           │   │
│ │ 2027           │   │
│ │ 2028           │   │
│ └────────────────┘   │
└──────────────────────┘
```

**Features**:
- Dynamic range: Current year ±2 years
- Currently: 2024, 2025, 2026, 2027, 2028
- Current year pre-selected
- Automatically updates as years change

---

### 3. Department Filter
```
┌──────────────────────┐
│ Department           │
│ ┌────────────────┐   │
│ │ All Depts (✓) │   │
│ │ Teaching       │   │
│ │ Library        │   │
│ │ Admin          │   │
│ │ Support        │   │
│ │ Finance        │   │
│ └────────────────┘   │
└──────────────────────┘
```

**Features**:
- 6 options total
- "All Departments" = no filter (default)
- All departments from sample data
- Case-sensitive matching with database

---

### 4. Apply Filter Button
```
┌─────────────────────┐
│ 🔍 Apply Filter    │
│ (Blue button)       │
└─────────────────────┘
```

**Action**:
- Collects values from all 3 dropdowns
- Updates calendar for selected month/year
- Applies department filter to staff list
- Updates calendar title

**Color**: Bootstrap primary (blue)

---

### 5. Reset Button
```
┌────────────────┐
│ 🔄 Reset       │
│ (Gray button)  │
└────────────────┘
```

**Action**:
- Resets month to current month
- Resets year to current year
- Clears department filter ("All Departments")
- Reloads calendar for today

**Color**: Bootstrap secondary (gray)

---

## 🔄 How It Works

### User Flow:

```
1. Page loads
   ↓
2. Current month/year/dept selected
   ↓
3. User clicks "Apply Filter"
   ↓
4. JavaScript reads dropdown values
   ↓
5. Calendar updates to show selected month
   ↓
6. All staff members displayed
   ↓
7. Attendance for selected month shown
   ↓
8. [Optional] Filter by department
   ↓
9. User clicks "Reset"
   ↓
10. All filters cleared, returns to today
```

### JavaScript Functions Involved:

1. **populateFilterYearDropdown()** (Line ~925)
   - Creates year dropdown options
   - Runs on page load
   - Range: currentYear - 2 to currentYear + 2

2. **applyMonthFilter()** (Line ~1002)
   - Event listener on "Apply Filter" button
   - Reads month/year/dept from dropdowns
   - Updates currentCalendarDate
   - Calls loadMonthlyCalendar()

3. **resetMonthFilter()** (Line ~1011)
   - Event listener on "Reset" button
   - Sets dropdowns to current date values
   - Clears department filter
   - Calls loadMonthlyCalendar()

4. **loadMonthlyCalendar()** (Line ~920)
   - Main function that updates calendar
   - Calls generateCalendarView()

5. **generateCalendarView()** (Line ~1077)
   - Creates calendar table
   - Generates date headers for selected month
   - Populates staff rows
   - Adds attendance badges

---

## 💻 HTML Structure

```html
<div class="card mt-4 mb-3">
  <div class="card-header bg-white">
    <h6 class="mb-3">Filter by Month & Year</h6>
    
    <div class="row g-3">
      <!-- Month Column -->
      <div class="col-md-3">
        <label for="filterMonth" class="form-label">Month</label>
        <select id="filterMonth" class="form-select">
          <option value="0">January</option>
          <option value="1">February</option>
          <!-- ... 12 options total ... -->
        </select>
      </div>
      
      <!-- Year Column -->
      <div class="col-md-3">
        <label for="filterYear" class="form-label">Year</label>
        <select id="filterYear" class="form-select">
          <!-- Years will be populated by JavaScript -->
        </select>
      </div>
      
      <!-- Department Column -->
      <div class="col-md-3">
        <label for="filterDept" class="form-label">Department</label>
        <select id="filterDept" class="form-select">
          <option value="">All Departments</option>
          <option value="Teaching">Teaching</option>
          <option value="Library">Library</option>
          <option value="Admin">Admin</option>
          <option value="Support">Support</option>
          <option value="Finance">Finance</option>
        </select>
      </div>
      
      <!-- Buttons Column -->
      <div class="col-md-3 d-flex align-items-end gap-2">
        <button id="applyMonthFilter" class="btn btn-primary flex-grow-1">
          <i class="fas fa-filter"></i> Apply Filter
        </button>
        <button id="resetMonthFilter" class="btn btn-secondary">
          <i class="fas fa-redo"></i> Reset
        </button>
      </div>
    </div>
  </div>
</div>
```

---

## 📱 Responsive Design

### Desktop (Col-md-3: 25% width each)
```
[Month      ] [Year       ] [Department] [Buttons    ]
    25%          25%           25%          25%
```

### Tablet (Col-sm-6: 50% width each)
```
[Month      ] [Year       ]
    50%          50%

[Department ] [Buttons    ]
    50%          50%
```

### Mobile (Col-xs-12: 100% width each)
```
[Month              ]
    100%

[Year               ]
    100%

[Department         ]
    100%

[Buttons            ]
    100%
```

---

## 🎨 Styling Details

### Filter Card
- Background: White
- Border: Light gray (Bootstrap card)
- Padding: Standard spacing
- Margin-top: 4 (mt-4)
- Margin-bottom: 3 (mb-3)

### Form Labels
- Font-size: 0.875rem (small)
- Font-weight: 500 (medium)
- Color: Dark gray
- Margin-bottom: 0.5rem

### Dropdowns (form-select)
- Background: White
- Border: Light gray
- Padding: 0.375rem 0.75rem (medium)
- Font-size: 1rem
- Hover effect: Border darkens
- Focus effect: Blue border, box shadow

### Buttons
- **Apply Filter**:
  - Background: Bootstrap primary (blue #0d6efd)
  - Text: White
  - Icon: Font Awesome filter icon
  - Width: flex-grow-1 (takes available space)
  - Hover: Darker blue

- **Reset**:
  - Background: Bootstrap secondary (gray #6c757d)
  - Text: White
  - Icon: Font Awesome redo/refresh icon
  - Width: Fixed (not flex)
  - Hover: Darker gray

---

## 🧪 Testing the Filter

### Test 1: Change Month
1. Click Month dropdown
2. Select "March"
3. Click "Apply Filter"
4. Expected: Calendar shows March dates (1-31)

### Test 2: Change Year
1. Click Year dropdown
2. Select "2025"
3. Click "Apply Filter"
4. Expected: Calendar shows dates from 2025

### Test 3: Filter by Department
1. Click Department dropdown
2. Select "Teaching"
3. Click "Apply Filter"
4. Expected: Only teaching staff shown (fewer rows)

### Test 4: Reset All
1. Make any changes above
2. Click "Reset" button
3. Expected: All dropdowns reset to current date
4. Expected: All staff shown again

### Test 5: Combined Filter
1. Select Month: "April"
2. Select Year: "2026"
3. Select Department: "Admin"
4. Click "Apply Filter"
5. Expected: April 2026 dates shown
6. Expected: Only Admin staff shown

---

## 📊 Sample Values in Dropdowns

### Month Options (12 total)
```
Value  Display
  0    January
  1    February (currently selected)
  2    March
  3    April
  4    May
  5    June
  6    July
  7    August
  8    September
  9    October
 10    November
 11    December
```

### Year Options (Dynamic, ±2 years)
```
If current year is 2026:
  2024
  2025
  2026 (currently selected)
  2027
  2028
```

### Department Options (6 total)
```
Value        Display
 (empty)     All Departments (default)
 Teaching    Teaching
 Library     Library
 Admin       Admin
 Support     Support
 Finance     Finance
```

---

## 🚀 What Happens When You Click Apply Filter

```
User clicks "Apply Filter"
    ↓
JavaScript event listener triggered
    ↓
Read month value: document.getElementById('filterMonth').value
Read year value: document.getElementById('filterYear').value
Read department value: document.getElementById('filterDept').value
    ↓
Create new date: new Date(year, month, 1)
    ↓
Update global variable: currentCalendarDate = new Date(...)
    ↓
Call loadMonthlyCalendar()
    ↓
Function generateCalendarView(currentCalendarDate)
    ↓
Calculate number of days in selected month
    ↓
Generate date header row (1st-31st)
    ↓
Generate staff rows with attendance badges
    ↓
Calendar table updates in browser
    ↓
User sees new month/year/department data
```

---

## 🔧 Customization

### Add More Departments
Edit lines 613-618:
```html
<option value="NewDept">Display Name</option>
```

Example:
```html
<option value="IT">IT Department</option>
```

### Change Filter Labels
Edit label text in HTML (lines 598, 606, 614)

### Change Button Icons
Edit Font Awesome icon classes:
```html
<i class="fas fa-filter"></i>   <!-- Filter icon -->
<i class="fas fa-redo"></i>     <!-- Refresh icon -->
```

Other options:
```
fa-funnel          (alternate filter)
fa-sliders-h       (sliders)
fa-search          (search)
fa-sync            (sync/refresh)
fa-arrow-left      (back/reset)
```

### Change Button Colors
Edit button classes:
```html
btn-primary        (blue)
btn-secondary      (gray)
btn-success        (green)
btn-danger         (red)
btn-warning        (yellow)
btn-info           (light blue)
```

---

## 📝 Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| Filter doesn't update calendar | Event listener not attached | Check line 1002-1008 |
| Year dropdown empty | populateFilterYearDropdown() not called | Check line 869 |
| Department filter shows nothing | Department name mismatch | Verify spelling matches sample data |
| Buttons not clickable | CSS issue | Check button styles |
| Dropdown values not changing | JavaScript error | Check browser console (F12) |

---

## 🎯 Design Philosophy

✅ **Simple**: Just month, year, department
✅ **Intuitive**: Familiar dropdown format
✅ **Fast**: One-click filtering
✅ **Responsive**: Works on all screen sizes
✅ **Accessible**: Proper labels and semantic HTML
✅ **Beautiful**: Bootstrap 5 styling

---

## 📖 Related Documentation

- **Frontend Features**: See `STAFF_ATTENDANCE_SUMMARY.md` → "Frontend Capabilities"
- **JavaScript Code**: See `staff_attendence.php` lines 591-630 and 994-1041
- **Integration**: See `ATTENDANCE_SYSTEM_INTEGRATION_GUIDE.md` → "Part 4"
- **Quick Start**: See `QUICK_SETUP_GUIDE.md` → "Filter Features"

---

**Filter Section Complete! 🎉**

The filter works with sample data right now. To connect it to real database data, see the integration guide.
