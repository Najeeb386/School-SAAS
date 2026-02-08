# 🔧 Exam Assignment API - Troubleshooting Guide

## What Was Fixed

1. ✅ **Database Connection Pattern** - Changed from `new Database()` to `Database::connect()` (singleton pattern)
2. ✅ **Enhanced Debugging** - Added console.log statements with emojis to track data loading
3. ✅ **API Response Logging** - Added debug info to API responses to show record counts
4. ✅ **Auto-Open Modal** - Improved logic to auto-open assignment modal when exam_id is in URL
5. ✅ **Better Error Messages** - All fetch calls now show detailed error responses

## Step 1: Test the Diagnostic Page

Navigate to:
```
http://your-domain/School-SAAS/App/Modules/School_Admin/Views/examination/generate_exam/test_api.php
```

**What to check:**
- ✅ School ID displays correctly
- ✅ Database connection shows "Connected"
- ✅ Classes count > 0
- ✅ Subjects count > 0
- ✅ Exams count > 0

**If counts are 0:** The database doesn't have data for your school. You need to:
- Create classes in the system
- Create subjects
- Create exams

---

## Step 2: Check Browser Console

1. Open your browser **Developer Tools** (F12)
2. Go to **Console** tab
3. Load `assign_to_class.php` and watch for:

### Expected Output (Success):
```
🚀 Page loaded. examIdFromUrl = null
🔵 Loading classes...
Classes response status: 200
✅ Classes data received: {success: true, data: Array(3), count: 3, ...}
✅ Classes loaded: 3 classes
✅ Exams loaded: 2 exams
✅ Subjects loaded successfully: 5 subjects
```

### If You See Errors:
```
❌ Fetch error loading classes: HTTP 500: ...
```
→ This means the API is returning an error. Check the error message!

---

## Step 3: Check Network Tab

1. In Developer Tools, go to **Network** tab
2. Reload the page
3. Look for requests to `manage_exam_assignments.php`
4. Click on each request and check:
   - **Status**: Should be 200 (not 500)
   - **Response**: Should be valid JSON with `success: true`

---

## Step 4: If Nothing Loads

### Problem: Empty dropdowns

**Solution 1: Check Database Data**
```sql
-- Run this in your database
SELECT COUNT(*) FROM school_classes WHERE school_id = YOUR_SCHOOL_ID AND status = 'active';
SELECT COUNT(*) FROM school_subjects WHERE school_id = YOUR_SCHOOL_ID AND status = 'active';
SELECT COUNT(*) FROM school_exams WHERE school_id = YOUR_SCHOOL_ID AND status = 'active';
```

**Solution 2: Create Test Data**
- Go to your School Admin Dashboard
- Create a class (e.g., "Class 10")
- Create subjects (e.g., "Mathematics", "English")
- Create an exam (e.g., "Mid-Term Exam")

**Solution 3: Check Session**
Add this to browser console:
```javascript
fetch('./manage_exam_assignments.php?action=health')
  .then(r => r.json())
  .then(d => console.log('Health Check:', d))
  .catch(e => console.error('Health error:', e));
```

Should show your school_id.

---

## Step 5: Test Auto-Selection from URL

1. Go to **Generate Exam** page
2. Click **Details** button on any exam
3. Should auto-open the assignment modal with that exam selected

**If not working:**
- Check console for errors
- Verify exam_id is in URL: `assign_to_class.php?exam_id=2`

---

## File Structure

```
manage_exam_assignments.php
├── Loads Database (singleton pattern)
├── Loads Controller
└── Routes to Controller methods
    ├── get_classes
    ├── get_sections  
    ├── get_subjects
    ├── get_assignments
    ├── save_assignment
    ├── delete_assignment
    └── health (diagnostic)

ExamAssignmentController.php
├── Constructor loads Model
└── Methods:
    ├── getClasses()
    ├── getSectionsByClass()
    ├── getSubjects()
    ├── saveAssignment()
    ├── getAssignments()
    └── deleteAssignment()

ExamAssignmentModel.php
└── Database queries:
    ├── getClasses()
    ├── getSectionsByClass()
    ├── getSubjects()
    ├── saveExamClass()
    ├── saveExamSubjects()
    ├── getAssignments()
    └── deleteExamClass()
```

---

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "All subjects already added" | Subjects loaded from DB. Verify you created subjects. |
| Classes dropdown empty | Database has no classes. Create classes in School Admin. |
| Modal won't open | Check browser console for JavaScript errors. |
| 500 errors in Network tab | Check error message - usually database query error. |
| Exam not auto-selected | Verify exam_id is in URL parameter. |

---

## Debug Commands

Run these in browser console:

```javascript
// Check what data was loaded
console.log('Classes:', allClasses);
console.log('Subjects:', allSubjects);
console.log('Exams:', allExams);

// Test API directly
fetch('./manage_exam_assignments.php?action=get_classes')
  .then(r => r.json())
  .then(d => console.log('API Classes Response:', d));

// Check exam from URL
console.log('Exam ID from URL:', examIdFromUrl);

// Check session
fetch('./manage_exam_assignments.php?action=health')
  .then(r => r.json())
  .then(d => console.log('Session School ID:', d.school_id));
```

---

## Next Steps

1. ✅ Open `test_api.php` to validate database has data
2. ✅ Check browser console for loading messages
3. ✅ Check Network tab for 200 responses
4. ✅ If no data: Create classes/subjects/exams
5. ✅ If data exists: Check error messages and report back

Report the specific error message and we'll fix it!
