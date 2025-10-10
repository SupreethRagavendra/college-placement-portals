# LIMITED MODE Fix - Quick Reference Card

## 🚨 The Problem
```
Error: column "title" does not exist
Error: column "duration" does not exist
```

## ✅ The Solution

### Database Columns (Use These in SQL)
```php
name        // NOT 'title'
total_time  // NOT 'duration'
```

### Quick Fix Cheatsheet

| Instead of... | Use... |
|--------------|--------|
| `select('title')` | `select('name')` |
| `select('duration')` | `select('total_time')` |
| `with('assessment:id,title')` | `with('assessment:id,name')` |
| `with('assessment:id,duration')` | `with('assessment:id,total_time')` |

## 📝 Code Examples

### ❌ WRONG
```php
Assessment::select('id', 'title', 'category', 'duration')->get();
```

### ✅ RIGHT
```php
Assessment::select('id', 'name', 'category', 'total_time')->get();
```

### ✅ WITH FALLBACK
```php
$assessment = Assessment::select('id', 'name', 'total_time')->first();
$name = $assessment->name ?? $assessment->title ?? 'Unknown';
$time = $assessment->total_time ?? $assessment->duration ?? 30;
```

## 🧪 Quick Test
```bash
php test-limited-mode-fix.php
```

## 📊 Status
- File: `app/Http/Controllers/Student/OpenRouterChatbotController.php`
- Lines: ~30 changes
- Status: ✅ FIXED
- Tests: ✅ ALL PASSING

## 🎯 Remember
**Accessors work AFTER retrieval, not in SQL!**

```php
// ✅ Query with real columns
$a = Assessment::select('name')->first();

// ✅ Access via accessor
echo $a->title;  // Works! (accessor)
```

---
**Quick Ref Version 1.0** | Oct 9, 2025

