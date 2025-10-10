# Questions Table - Actual Structure

## Database Columns (Based on Migrations)

### Original Table (2025_09_06_100000)
- `id` - Primary key
- `question_text` - Text
- `options` - JSON array (stores all 4 options)
- `correct_option` - Integer (0-3 index)
- `category` - Enum ('Aptitude', 'Technical')
- `difficulty` - Enum ('Easy', 'Medium', 'Hard')
- `time_per_question` - Integer (seconds)
- `is_active` - Boolean
- `created_at` - Timestamp
- `updated_at` - Timestamp

### Added by Migrations
- `question_type` - Enum ('mcq', 'true_false', 'short_answer') - from 2025_09_29_150005
- `marks` - Integer - from 2025_09_29_150005
- `difficulty_level` - Enum ('easy', 'medium', 'hard') - from 2025_09_29_150005
- `order` - Integer - from 2025_09_29_150005
- `assessment_id` - Foreign key (nullable) - from 2025_09_29_150008
- `category_id` - Foreign key - from 2025_10_03_135347

## Important Notes

### ❌ DOES NOT HAVE:
- `option_a` - Individual option column
- `option_b` - Individual option column
- `option_c` - Individual option column
- `option_d` - Individual option column
- `question` - Field (uses `question_text` instead)
- `correct_answer` - Field (uses `correct_option` index instead)
- `created_by` - Field

### ✅ Options Storage
Options are stored as **JSON array** in the `options` column:
```json
["Option A text", "Option B text", "Option C text", "Option D text"]
```

### ✅ Correct Answer Storage
Stored as **index** (0-3) in `correct_option`:
- 0 = Option A
- 1 = Option B
- 2 = Option C
- 3 = Option D

## Correct Way to Create Questions

```php
Question::create([
    'question_text' => 'What is 2+2?',
    'question_type' => 'mcq',
    'options' => ['3', '4', '5', '6'],  // JSON array
    'correct_option' => 1,               // Index (B = 1)
    'marks' => 1,
    'difficulty_level' => 'easy',
    'difficulty' => 'Easy',              // Capitalized for original column
    'category' => 'Aptitude',
    'category_id' => 1,
    'assessment_id' => 17,
    'time_per_question' => 60,
    'is_active' => true,
    'order' => 0,
]);
```

## Why Two Difficulty Columns?

- `difficulty` - Original column (Enum: 'Easy', 'Medium', 'Hard') - **Capitalized**
- `difficulty_level` - Added later (Enum: 'easy', 'medium', 'hard') - **Lowercase**

Both are kept for backward compatibility.

## Why No option_a, option_b, etc.?

The original design used JSON to store options, which is:
- ✅ More flexible
- ✅ Easier to add more options later
- ✅ Cleaner database structure
- ✅ Less redundant data

## Model Fillable Array

The Question model has many fields in `$fillable` that don't exist in the database. This is okay - Laravel will ignore fields that don't exist in the table.

However, when creating questions, **only use fields that actually exist** in the database.

---

**Status:** ✅ Controller Updated  
**Date:** October 3, 2025
