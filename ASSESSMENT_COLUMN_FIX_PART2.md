# Assessment Column Name Fix - Part 2

## Issue
```
SQLSTATE[HY000]: General error: 1 table assessments has no column named time_limit (Connection: sqlite, SQL: insert into "assessments" ("title", "description", "category", "time_limit", "is_active", "updated_at", "created_at") values (Java, test, Technical, 30, 1, 2025-09-29 14:16:01, 2025-09-29 14:16:01))
```

## Root Cause
There was still a mismatch between the column names in the database schema and the code:
- Database column: `total_time`
- Code was still trying to use: `time_limit`

## Files Fixed

### 1. Models
1. `app\Models\Assessment.php`
   - Fixed accessor/mutator methods for total_time mapping: `time_limit` → `total_time`

### 2. Views
1. `resources\views\admin\assessments\add-question.blade.php`
   - Fixed displayed values: `time_limit` → `total_time`

2. `resources\views\admin\assessments\questions.blade.php`
   - Fixed displayed values: `time_limit` → `total_time`

3. `resources\views\student\assessments\index.blade.php`
   - Fixed displayed values: `time_limit` → `total_time`

4. `resources\views\student\assessments\show.blade.php`
   - Fixed displayed values: `time_limit` → `total_time`

5. `resources\views\student\dashboard.blade.php`
   - Fixed displayed values: `time_limit` → `total_time`

### 3. Seeders
1. `database\seeders\AssessmentSeeder.php`
   - Fixed column names: `name` → `title`, `time_limit` → `total_time`

## Verification
After implementing these changes, the assessment creation should work correctly without the column name error.

## Prevention
To prevent similar issues in the future:
1. Always verify database schema matches model definitions
2. Use consistent naming conventions throughout the application
3. Test CRUD operations after making schema changes
4. Update all references when changing column names