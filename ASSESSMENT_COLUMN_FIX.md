# Assessment Column Name Fix

## Issue
```
SQLSTATE[HY000]: General error: 1 table assessments has no column named name (Connection: sqlite, SQL: insert into "assessments" ("name", "description", "category", "time_limit", "is_active", "updated_at", "created_at") values (Java-test-1, java test 1, Technical, 10, 1, 2025-09-29 14:06:42, 2025-09-29 14:06:42))
```

## Root Cause
There was a mismatch between the column names in the database schema and the code:
- Database column: `title`
- Code was trying to use: `name`

- Database column: `total_time`
- Code was trying to use: `time_limit`

## Files Fixed

### 1. Models
1. `app\Models\Assessment.php`
   - Fixed fillable attributes: `name` → `title`, `time_limit` → `total_time`
   - Fixed accessor/mutator methods for title mapping
   - Fixed casts: `time_limit` → `total_time`

### 2. Controllers
1. `app\Http\Controllers\AdminAssessmentController.php`
   - Fixed store method: `name` → `title`, `time_limit` → `total_time`
   - Fixed update method: `name` → `title`, `time_limit` → `total_time`

### 3. Views
1. `resources\views\admin\assessments\create.blade.php`
   - Already had correct field names

2. `resources\views\admin\assessments\edit.blade.php`
   - Fixed field values: `name` → `title`, `time_limit` → `total_time`

3. `resources\views\admin\assessments\index.blade.php`
   - Fixed displayed values: `name` → `title`, `time_limit` → `total_time`

4. `resources\views\admin\assessments\show.blade.php`
   - Fixed displayed values: `name` → `title`, `time_limit` → `total_time`

### 4. Controllers (Additional)
1. `app\Http\Controllers\StudentController.php`
   - Fixed references to assessment attributes

## Verification
After implementing these changes, the assessment creation should work correctly without the column name error.

## Prevention
To prevent similar issues in the future:
1. Always verify database schema matches model definitions
2. Use consistent naming conventions throughout the application
3. Test CRUD operations after making schema changes