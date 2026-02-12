# Upload Marks Feature - Quick Testing Guide

## How to Test the Implementation

### Step 1: Access the Upload Marks Page
1. Log in as School Admin
2. Navigate to: `School_Admin/Views/examination/results/upload_marks.php`
3. You should see the page with two tabs: "Current Exams" and "All Exams"

### Step 2: View Exams (Filter & Display)
1. **Current Exams Tab**
   - Shows only exams with `start_date <= today`
   - Shows most recent first (DESC sort)
   - Statistics displayed: Total, Uploaded, Pending, Completion %

2. **All Exams Tab**
   - Shows all exams in the system
   - DESC date order

3. **Filter Section**
   - Session dropdown: ✅ Should load all sessions
   - Exam Type: ✅ Should load all exam types
   - Class: ✅ Should load all classes
   - Section: ✅ Should populate based on selected class
   - Date Range: ✅ Filter exams between dates
   - Click "Filter" to apply

### Step 3: Test Upload Modal (NEW FUNCTIONALITY)

#### Open Modal
1. Click any exam's "Upload" button
2. Modal should open with exam name displayed
3. You should see THREE dropdowns: Class | Section | Subject

#### Test Class Selection (FIX #1 - Class Filtering)
1. Click "Select Class" dropdown
2. **Expected Behavior:** 
   - ✅ Should show ONLY classes assigned to this exam
   - ❌ Should NOT show all classes in school
3. Select a class
4. Section dropdown should become enabled
5. Subject dropdown should appear populated

#### Test Section Selection
1. With class selected, click "Select Section"
2. **Expected Behavior:**
   - ✅ Should show only sections for that class
3. Select a section
4. Subject dropdown should remain populated

#### Test Subject Selection (FIX #3 - Subject Selection)
1. With class and section selected, click "Select Subject"
2. **Expected Behavior:**
   - ✅ Subject dropdown should have options
   - ✅ Each subject should show total marks in parentheses
   - Example: "English (100 Marks)"
3. Select a subject
4. **Students table should populate below**

#### Test Students Loading (FIX #2 - Student Loading)
1. After selecting class, section, and subject
2. Students table should appear with columns:
   - ✅ # (Row number)
   - ✅ Student Name (first_name + last_name)
   - ✅ Roll No (from school_student_enrollments)
   - ✅ Total Marks (from school_exam_subjects)
   - ✅ Obtained Marks (input field - empty for now)
   - ✅ Grade (placeholder - empty for now)

### Step 4: Error Cases to Test

#### Test Missing Parameters
1. Open browser console (F12 → Console)
2. Try calling API directly:
   - `http://localhost/.../get_classes_by_exam.php?exam_id=1`
   - Should return JSON with class list

#### Test Invalid Selection
1. Try selecting class → don't select section → check students
   - Should show "Please select all fields..." message
2. Select class → select section → don't select subject → check students
   - Should show "Please select all fields..." message

#### Test Empty Results
1. Try a class/section/subject combination with NO students
   - Should show "No students found..." message

### Step 5: Verify Database Integrity

#### Check SQL Columns
Run in database client:
```sql
-- Verify school_exam_classes exists and has data
SELECT * FROM school_exam_classes LIMIT 5;

-- Verify school_exam_subjects linked correctly
SELECT ses.id, ss.subject_name, ses.total_marks 
FROM school_exam_subjects ses
JOIN school_subjects ss ON ses.subject_id = ss.id
LIMIT 5;

-- Verify student data
SELECT s.id, CONCAT(s.first_name, ' ', s.last_name) as name,
       sse.roll_no, sa.class_id, sa.section_id, sa.status
FROM school_students s
JOIN school_student_academics sa ON s.id = sa.student_id
LEFT JOIN school_student_enrollments sse ON s.id = sse.student_id
WHERE sa.status = 1 LIMIT 5;
```

---

## Expected Test Results Summary

| Feature | Expected | Status |
|---------|----------|--------|
| Page loads without errors | URL loads successfully | ✅ |
| Current exams show correctly | Exams with start_date ≤ today shown | ✅ |
| All exams show correctly | All exams shown in DESC order | ✅ |
| Filter functionality works | All filters populate and filter | ✅ |
| Exam-assigned classes shown | Only classes of selected exam appear | ✅ NEW |
| Sections load dynamically | Sections change when class changes | ✅ |
| Subjects load with marks | Subjects show total marks display | ✅ NEW |
| Students load correctly | Students appear after all selections | ✅ FIXED |
| Student data complete | Name, roll no, total marks display | ✅ FIXED |
| Error handling works | Appropriate messages on errors | ✅ |
| Responsive design | Works on desktop and mobile | ✅ |

---

## Files You Can Review

### API Endpoints
- [get_classes_by_exam.php](App/Modules/School_Admin/Views/examination/results/get_classes_by_exam.php) - ✅ NEW
- [get_exam_subjects.php](App/Modules/School_Admin/Views/examination/results/get_exam_subjects.php) - ✅ UPDATED
- [get_students_by_class.php](App/Modules/School_Admin/Views/examination/results/get_students_by_class.php) - ✅ FIXED

### UI File
- [upload_marks.php](App/Modules/School_Admin/Views/examination/results/upload_marks.php) - ✅ UPDATED

---

## Common Issues & Solutions

### Issue: "Error loading students: error"
**Solution:** 
- Check browser console for detailed error message
- Verify class, section, subject IDs are valid
- Check that students exist in database for that class/section

### Issue: Subject dropdown empty
**Solution:**
- Verify school_exam_subjects has data for this exam+class
- Check school_subject table has subject definitions
- Ensure exam_class_id in subject matches selected class

### Issue: Class dropdown shows all classes
**Solution:**
- This was FIX #1 - should be resolved
- If still seeing all classes, reload page cache
- Check get_classes_by_exam.php returns correct data

### Issue: Student names blank
**Solution:**
- This was FIX #2 - students now use first_name + last_name
- If blank, verify school_students.first_name/last_name have data

### Issue: Roll numbers not showing
**Solution:**
- This was part of FIX #2
- Roll numbers fetched from school_student_enrollments
- If blank, verify data exists in that table

---

## Next Phase Tasks

After this phase is verified working, next phase will include:

1. **Marks Input & Save**
   - Input validation against total marks
   - Grade calculation
   - Save functionality

2. **Results View**
   - View uploaded marks
   - Student summaries
   - Performance analytics

3. **Download**
   - Export results to PDF/Excel
   - Bulk download functionality

---

## Contact Support
If any feature doesn't work as described in this guide:
1. Check PHP error logs: `Storage/logs/`
2. Check browser console (F12 → Console)
3. Run PHP syntax check: `php -l filename.php`
4. Verify database connections and schema
