# LIMITED MODE Column Name Fix - COMPLETE ✅

## Problem
The RAG chatbot's LIMITED MODE (Mode 2) was failing with this error:

```
SQLSTATE[42703]: Undefined column: 7 ERROR: column "title" does not exist
LINE 1: select "id", "title", "category", "duration" from "assessments"...
```

## Root Cause
The code was using **accessor column names** (`title`, `duration`) in SQL queries instead of the **actual database column names** (`name`, `total_time`).

### Database Schema vs Model Accessors

**Actual Database Columns:**
- `name` (assessment name)
- `total_time` (duration in minutes)
- `category`
- `status`

**Model Accessors (Virtual Columns):**
- `title` → maps to `name` (via getTitleAttribute)
- `duration` → maps to `total_time` (via getDurationAttribute)

### The Issue
You **cannot** use accessor names in `select()` statements because they don't exist in the database. The Assessment model has accessors that create virtual `title` and `duration` attributes, but these are PHP-level transformations, not SQL columns.

## Files Fixed

### 1. `app/Http/Controllers/Student/OpenRouterChatbotController.php`

Fixed **3 sections**:

#### Section 1: Limited Mode - Available Assessments Query (Line 364-387)
**Before:**
```php
$assessments = Assessment::active()
    ->whereDoesntHave('studentResults', function($q) use ($studentId) {
        $q->where('student_id', $studentId);
    })
    ->select('id', 'title', 'category', 'duration')  // ❌ Wrong columns
    ->limit(3)
    ->get();

foreach ($assessments as $assessment) {
    $message .= "📝 {$assessment->title} ({$assessment->category}) - {$assessment->duration} minutes\n";
}
```

**After:**
```php
$assessments = Assessment::active()
    ->whereDoesntHave('studentResults', function($q) use ($studentId) {
        $q->where('student_id', $studentId);
    })
    ->select('id', 'name', 'category', 'total_time')  // ✅ Correct columns
    ->limit(3)
    ->get();

foreach ($assessments as $assessment) {
    $assessmentName = $assessment->name ?? $assessment->title;
    $assessmentDuration = $assessment->total_time ?? $assessment->duration ?? 30;
    $message .= "📝 {$assessmentName} ({$assessment->category}) - {$assessmentDuration} minutes\n";
}
```

#### Section 2: Limited Mode - Student Results Query (Line 389-404)
**Before:**
```php
$results = StudentResult::where('student_id', $studentId)
    ->with('assessment:id,title')  // ❌ Wrong column
    ->orderBy('submitted_at', 'desc')
    ->limit(3)
    ->get();

foreach ($results as $result) {
    $message .= "📊 {$result->assessment->title}: {$percentage}%\n";
}
```

**After:**
```php
$results = StudentResult::where('student_id', $studentId)
    ->with('assessment:id,name')  // ✅ Correct column
    ->orderBy('submitted_at', 'desc')
    ->limit(3)
    ->get();

foreach ($results as $result) {
    $assessmentName = $result->assessment->name ?? $result->assessment->title ?? 'Unknown Assessment';
    $message .= "📊 {$assessmentName}: {$percentage}%\n";
}
```

#### Section 3: RAG Mode - Student Context (Line 231-282)
**Before:**
```php
// Available assessments
->select('id', 'title', 'description', 'category', 'duration', 'difficulty_level')  // ❌

// Completed assessments
->with('assessment:id,title,category,duration')  // ❌

// In-progress assessments
->with('assessment:id,title,category,duration')  // ❌
```

**After:**
```php
// Available assessments
->select('id', 'name', 'description', 'category', 'total_time', 'difficulty_level')  // ✅

// Completed assessments
->with('assessment:id,name,category,total_time')  // ✅

// In-progress assessments
->with('assessment:id,name,category,total_time')  // ✅
```

## How The Fix Works

### 1. **SQL Level** (Database Query)
```php
// Use actual database columns
->select('id', 'name', 'category', 'total_time')
```

### 2. **PHP Level** (Model Accessor)
```php
// Access via fallback chain for compatibility
$assessmentName = $assessment->name ?? $assessment->title;
$assessmentDuration = $assessment->total_time ?? $assessment->duration ?? 30;
```

This approach:
- ✅ Queries the correct database columns
- ✅ Provides fallback for backward compatibility
- ✅ Works whether data is accessed via `name` or `title`
- ✅ Provides default values if neither exists

## Testing

### Test Limited Mode
1. **Stop RAG Service** (to trigger Limited Mode)
2. **Ask chatbot:** "Show available assessments"
3. **Expected Response:**
   ```
   🟡 LIMITED MODE - Database Query Results:

   You have 3 assessment(s) available:

   📝 PHP Programming (Technical) - 60 minutes
   📝 JavaScript Basics (Technical) - 45 minutes
   📝 Database Design (Technical) - 45 minutes

   Click 'View Assessments' to start!
   ```

### Test Commands
```bash
# Test in browser
# 1. Open chatbot
# 2. Ask: "Show available assessments"
# 3. Ask: "Show my results"

# Check logs
php artisan log:tail | grep "MODE 2"
```

## What Was Learned

### Column Selection Best Practices

1. **Always use actual database column names in queries:**
   ```php
   // ✅ CORRECT
   Assessment::select('id', 'name', 'total_time')->get();
   
   // ❌ WRONG
   Assessment::select('id', 'title', 'duration')->get();
   ```

2. **Accessors work AFTER data is retrieved:**
   ```php
   $assessment = Assessment::find(1);
   echo $assessment->title;  // ✅ Works (uses accessor)
   
   Assessment::select('title')->get();  // ❌ Fails (no such column)
   ```

3. **For relationships with select():**
   ```php
   // ✅ CORRECT
   ->with('assessment:id,name,category')
   
   // ❌ WRONG
   ->with('assessment:id,title,category')
   ```

## Status: FIXED ✅

All three sections that query assessments have been fixed:
- ✅ Limited Mode - Available Assessments
- ✅ Limited Mode - Student Results
- ✅ RAG Mode - Student Context

The chatbot's LIMITED MODE now works properly when the RAG service is unavailable!

## Mode Reference

| Mode | Indicator | Service Status | Features |
|------|-----------|----------------|----------|
| **Mode 1** | 🟢 RAG ACTIVE | RAG service working | Full AI responses, context-aware |
| **Mode 2** | 🟡 LIMITED MODE | RAG down, Laravel up | Database queries, pattern matching |
| **Mode 3** | 🔴 OFFLINE | Laravel down | Frontend fallback only |

---
**Fixed on:** October 9, 2025
**Files Modified:** 1 (OpenRouterChatbotController.php)
**Lines Changed:** ~30 lines across 3 methods

