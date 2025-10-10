# Timer Debug Guide - Complete Troubleshooting

## Current Issue
Timer shows but doesn't count down (stuck at 30:00).

## Debug Steps - Follow These Exactly

### Step 1: Open Browser Console

1. Visit `http://localhost:8000/student/test/1`
2. Press **F12** (or right-click → Inspect)
3. Click **Console** tab

### Step 2: Check Console Output

You should see detailed logs like this:

```
=== INITIALIZING TEST PAGE ===
Total questions: 10
Timer duration: 30 minutes
Timer seconds: 1800
First question displayed
✅ Timer element found: [object HTMLSpanElement]
Current timer text: 30:00
=== STARTING TIMER ===
Calling updateTimer() first time...
updateTimer() called - seconds: 1800 elapsed: 0
Setting timer display to: 30:00
Timer text now shows: 30:00
Timer: GREEN normal
✅ Timer updated. New seconds: 1799 New elapsed: 1
Setting up interval...
✅ Timer interval started with ID: 1
Timer will update every 1 second
===========================
⏱️ Timer tick - seconds remaining: 1799
updateTimer() called - seconds: 1799 elapsed: 1
Setting timer display to: 29:59
Timer text now shows: 29:59
Timer: GREEN normal
✅ Timer updated. New seconds: 1798 New elapsed: 2
⏱️ Timer tick - seconds remaining: 1798
updateTimer() called - seconds: 1798 elapsed: 2
Setting timer display to: 29:58
Timer text now shows: 29:58
...
```

### Step 3: Diagnose Issues

#### ❌ If you see: "TIMER ELEMENT NOT FOUND"
**Problem:** The timer HTML element is missing

**Solution:**
```bash
# Check if timer element exists in HTML
# In console, run:
document.getElementById('timer')
```

If it returns `null`, the HTML is broken. Check the blade file.

#### ❌ If you see: "Timer initialized with 0 minutes"
**Problem:** Duration is not being passed from controller

**Solution:** Check `$assessment->total_time` or `$assessment->duration` in database

#### ❌ If interval logs appear but timer doesn't update
**Problem:** JavaScript is running but display update fails

**Check:**
```javascript
// In console:
document.getElementById('timer').textContent = 'TEST';
```

If timer doesn't change to "TEST", CSS might be hiding it.

#### ❌ If no console logs appear at all
**Problem:** JavaScript not loading

**Check:**
1. View page source (Ctrl+U)
2. Search for `@section('scripts')`
3. Verify JavaScript is present
4. Check for syntax errors (red in console)

### Step 4: Manual Timer Test

Run these commands in the browser console:

```javascript
// 1. Check if timer element exists
let timer = document.getElementById('timer');
console.log('Timer element:', timer);

// 2. Check initial values
console.log('Total minutes:', totalMinutes);
console.log('Seconds:', seconds);
console.log('Elapsed:', elapsed);

// 3. Test updating timer manually
timer.textContent = '25:30';
console.log('Did timer change?', timer.textContent);

// 4. Call updateTimer manually
updateTimer();
console.log('After updateTimer, timer shows:', timer.textContent);

// 5. Check if interval is running
console.log('Timer interval ID:', timerInterval);

// 6. Force countdown
seconds = 10;
console.log('Set seconds to 10, watch for countdown...');
```

### Step 5: Common Problems & Solutions

#### Problem 1: "seconds is not defined"
**Cause:** Variables are not in global scope

**Fix:** Check that variables are declared with `let` outside the functions

#### Problem 2: Timer shows "NaN:NaN"
**Cause:** `totalMinutes` is not a valid number

**Debug:**
```javascript
console.log('Type of totalMinutes:', typeof totalMinutes);
console.log('Value of totalMinutes:', totalMinutes);
console.log('Parsed value:', parseInt(totalMinutes));
```

#### Problem 3: Timer updates once then stops
**Cause:** Interval not set up properly

**Debug:**
```javascript
console.log('Interval exists:', timerInterval !== null);
console.log('Interval ID:', timerInterval);

// Clear and restart
clearInterval(timerInterval);
timerInterval = setInterval(updateTimer, 1000);
console.log('Restarted with ID:', timerInterval);
```

#### Problem 4: "updateTimer is not defined"
**Cause:** Function is inside DOMContentLoaded and not accessible

**Test:**
```javascript
// This should work:
typeof updateTimer
// Should return: "function"

// If it returns "undefined", function scope is wrong
```

### Step 6: Force Timer to Work

If nothing else works, paste this in console:

```javascript
// EMERGENCY TIMER FIX
console.log('Starting emergency timer...');

let emergencySeconds = 1800; // 30 minutes

function emergencyTimer() {
    let m = Math.floor(emergencySeconds / 60);
    let s = emergencySeconds % 60;
    let display = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    
    let elem = document.getElementById('timer');
    if (elem) {
        elem.textContent = display;
        console.log('Emergency timer:', display);
    }
    
    if (emergencySeconds > 0) {
        emergencySeconds--;
    } else {
        alert('Time up!');
        clearInterval(emergencyInterval);
    }
}

let emergencyInterval = setInterval(emergencyTimer, 1000);
emergencyTimer(); // Call once immediately

console.log('Emergency timer started!');
```

This will bypass all the code and run a simple countdown.

### Step 7: Check PHP Variables

The timer gets its duration from PHP. Check what's being passed:

**In the blade file**, temporarily add:
```blade
<script>
console.log('PHP minutes value:', '{{ $minutes ?? "NOT SET" }}');
console.log('PHP minutes type:', typeof '{{ $minutes ?? 0 }}');
</script>
```

Reload and check console. Should show:
```
PHP minutes value: 30
PHP minutes type: string
```

### Step 8: Verify setInterval Works

Test if setInterval works at all in your browser:

```javascript
// Simple test
let testCounter = 0;
let testInterval = setInterval(function() {
    testCounter++;
    console.log('Test count:', testCounter);
    if (testCounter >= 5) {
        clearInterval(testInterval);
        console.log('Test interval works!');
    }
}, 1000);
```

You should see:
```
Test count: 1
Test count: 2
Test count: 3
Test count: 4
Test count: 5
Test interval works!
```

### Step 9: Check for JavaScript Errors

Look in the console for any red error messages:

- ❌ `Uncaught ReferenceError: X is not defined`
- ❌ `Uncaught TypeError: Cannot read property`
- ❌ `Uncaught SyntaxError`

Any error will stop JavaScript execution.

### Step 10: Nuclear Option - Start Fresh

If nothing works, replace the entire timer section with this minimal version:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    console.log('MINIMAL TIMER STARTING');
    
    let timerSeconds = 1800; // 30 minutes
    
    function tick() {
        let m = Math.floor(timerSeconds / 60);
        let s = timerSeconds % 60;
        document.getElementById('timer').textContent = 
            String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        
        console.log('Tick:', timerSeconds);
        timerSeconds--;
        
        if (timerSeconds < 0) {
            clearInterval(minimalInterval);
            alert('Time up!');
        }
    }
    
    tick(); // Show immediately
    let minimalInterval = setInterval(tick, 1000);
    console.log('Minimal timer running');
});
```

## Expected Behavior

**When working correctly, you should see:**

1. Page loads
2. Console shows initialization logs
3. Timer shows 30:00 (or whatever duration)
4. After 1 second: Timer changes to 29:59
5. After 2 seconds: Timer changes to 29:58
6. Console shows "⏱️ Timer tick" every second
7. Timer continues counting down

## If Timer Still Doesn't Work

### Share these details:

1. **Browser & Version:** (e.g., Chrome 120, Firefox 121)
2. **Console Output:** Copy all console messages
3. **Console Errors:** Copy any red error messages
4. **Network Tab:** Check if JavaScript files loaded (200 status)
5. **Element Check:** Result of `document.getElementById('timer')`
6. **Variable Check:** Values of `totalMinutes`, `seconds`, `timerInterval`

## Summary

The extensive logging should now show exactly where the timer fails:

- ✅ **Initialization** - Variables set correctly?
- ✅ **Element Found** - Timer HTML exists?
- ✅ **First Update** - updateTimer() called?
- ✅ **Interval Set** - setInterval created?
- ✅ **Tick Logs** - Updates happening?
- ✅ **Display Update** - Timer text changing?

Follow the console logs to find where it stops!

