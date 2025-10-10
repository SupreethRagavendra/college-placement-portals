# Security Implementation Guide - Non-Breaking Changes

## Quick Security Wins (Implement Today)

### 1. Environment Security (.env Protection)
```bash
# Create .env.example with dummy values
cp .env .env.example
sed -i 's/=.*/=/' .env.example

# Add to .gitignore
echo ".env" >> .gitignore
echo "*.key" >> .gitignore
echo "*.pem" >> .gitignore
echo "storage/*.key" >> .gitignore
```

### 2. Immediate Laravel Security Commands
```bash
# Generate new application key
php artisan key:generate

# Clear all caches for fresh start
php artisan optimize:clear

# Enable production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## Step-by-Step Implementation (Non-Breaking)

### Step 1: Add Security Middleware (No Breaking Changes)

```php
// app/Http/Middleware/SecurityHeaders.php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only add headers, don't modify functionality
        if (method_exists($response, 'header')) {
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            
            if ($request->secure()) {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }
        }
        
        return $response;
    }
}

// app/Http/Kernel.php - Add to $middleware array
protected $middleware = [
    // ... existing middleware
    \App\Http\Middleware\SecurityHeaders::class, // Add this line
];
```

### Step 2: Add Rate Limiting (Backwards Compatible)

```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot()
{
    $this->configureRateLimiting();
    // ... existing code
}

protected function configureRateLimiting()
{
    // Login attempts
    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)->by($request->email.$request->ip());
    });
    
    // API calls
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    });
    
    // Assessment submission
    RateLimiter::for('assessment', function (Request $request) {
        return Limit::perMinute(1)->by($request->user()->id);
    });
}

// Apply to routes without breaking existing ones
// routes/web.php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(['guest', 'throttle:login']); // Add throttle

Route::post('/student/assessments/{id}/submit', [StudentAssessmentController::class, 'submit'])
    ->middleware(['auth', 'throttle:assessment']); // Add throttle
```

### Step 3: Add Input Sanitization Layer (Non-Breaking)

```php
// app/Http/Middleware/SanitizeInput.php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    protected $except = [
        'password',
        'password_confirmation',
    ];
    
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value, $key) {
            if (!in_array($key, $this->except) && is_string($value)) {
                // Basic sanitization without breaking functionality
                $value = strip_tags($value);
                $value = trim($value);
                
                // Remove null bytes
                $value = str_replace(chr(0), '', $value);
            }
        });
        
        $request->merge($input);
        
        return $next($request);
    }
}

// Add to app/Http/Kernel.php in web middleware group
'web' => [
    // ... existing middleware
    \App\Http\Middleware\SanitizeInput::class,
],
```

### Step 4: Add Security Logging (Non-Intrusive)

```php
// app/Listeners/SecurityEventListener.php
<?php
namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;

class SecurityEventListener
{
    public function handleFailedLogin(Failed $event)
    {
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $event->credentials['email'] ?? 'unknown',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    public function handleSuccessfulLogin(Login $event)
    {
        Log::channel('security')->info('Successful login', [
            'user_id' => $event->user->id,
            'ip' => request()->ip(),
        ]);
    }
    
    public function handleLogout(Logout $event)
    {
        Log::channel('security')->info('User logout', [
            'user_id' => optional($event->user)->id,
        ]);
    }
}

// Register in app/Providers/EventServiceProvider.php
protected $listen = [
    Failed::class => [
        SecurityEventListener::class . '@handleFailedLogin',
    ],
    Login::class => [
        SecurityEventListener::class . '@handleSuccessfulLogin',
    ],
    Logout::class => [
        SecurityEventListener::class . '@handleLogout',
    ],
];

// Add security log channel to config/logging.php
'channels' => [
    // ... existing channels
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'info',
        'days' => 90,
        'permission' => 0644,
    ],
],
```

### Step 5: Add Session Security (Transparent)

```php
// app/Http/Middleware/SecureSession.php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureSession
{
    public function handle(Request $request, Closure $next)
    {
        // Regenerate session ID on login (non-breaking)
        if ($request->isMethod('post') && $request->is('login')) {
            $response = $next($request);
            
            if (auth()->check()) {
                $request->session()->regenerate();
            }
            
            return $response;
        }
        
        // Check for session timeout (30 minutes of inactivity)
        if (auth()->check()) {
            $lastActivity = session('last_activity');
            
            if ($lastActivity && time() - $lastActivity > 1800) {
                auth()->logout();
                $request->session()->invalidate();
                return redirect('/login')->with('message', 'Session expired due to inactivity');
            }
            
            session(['last_activity' => time()]);
        }
        
        return $next($request);
    }
}
```

### Step 6: Add CSRF Token Verification for AJAX (Non-Breaking)

```javascript
// public/js/secure-ajax-setup.js
// Add this to your main JavaScript file or include separately

(function() {
    // Store original fetch
    const originalFetch = window.fetch;
    
    // Override fetch to always include CSRF token
    window.fetch = function(url, options = {}) {
        // Only add CSRF for same-origin requests
        if (url.startsWith('/') || url.startsWith(window.location.origin)) {
            options.headers = options.headers || {};
            
            // Add CSRF token if not present
            if (!options.headers['X-CSRF-TOKEN']) {
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    options.headers['X-CSRF-TOKEN'] = token.content;
                }
            }
            
            // Add XMLHttpRequest header for Laravel
            options.headers['X-Requested-With'] = 'XMLHttpRequest';
        }
        
        return originalFetch(url, options);
    };
    
    // jQuery AJAX setup (if using jQuery)
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }
})();
```

### Step 7: Add Anti-Cheating for Assessments (Non-Intrusive)

```javascript
// public/js/assessment-security.js
class AssessmentSecurity {
    constructor() {
        this.warnings = 0;
        this.maxWarnings = 3;
        
        // Only monitor, don't break functionality
        this.initMonitoring();
    }
    
    initMonitoring() {
        // Monitor tab visibility (warning only)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && window.assessmentActive) {
                this.warnings++;
                console.warn('Tab switch detected during assessment');
                
                // Log but don't interrupt
                this.logActivity('tab_switch');
                
                if (this.warnings >= this.maxWarnings) {
                    this.showWarning('Multiple tab switches detected. This activity has been logged.');
                }
            }
        });
        
        // Disable right-click during assessment (non-breaking)
        document.addEventListener('contextmenu', (e) => {
            if (window.assessmentActive) {
                e.preventDefault();
                this.showWarning('Right-click is disabled during assessment');
                return false;
            }
        });
        
        // Warn about copy attempts
        document.addEventListener('copy', (e) => {
            if (window.assessmentActive) {
                e.preventDefault();
                this.showWarning('Copying is disabled during assessment');
                this.logActivity('copy_attempt');
                return false;
            }
        });
    }
    
    showWarning(message) {
        // Non-intrusive warning
        const warning = document.createElement('div');
        warning.className = 'assessment-warning';
        warning.textContent = message;
        warning.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f59e0b;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 9999;
            animation: fadeOut 3s forwards;
        `;
        
        document.body.appendChild(warning);
        setTimeout(() => warning.remove(), 3000);
    }
    
    logActivity(type) {
        // Log activity without breaking assessment
        fetch('/api/assessment/activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                type: type,
                timestamp: new Date().toISOString(),
                assessment_id: window.assessmentId
            })
        }).catch(() => {
            // Silently fail - don't break assessment
        });
    }
}

// Initialize only on assessment pages
if (window.location.pathname.includes('/assessments/take')) {
    window.assessmentSecurity = new AssessmentSecurity();
    window.assessmentActive = true;
}
```

### Step 8: Secure File Uploads (Backwards Compatible)

```php
// app/Services/SecureFileUpload.php
<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;

class SecureFileUpload
{
    protected $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    protected $maxSize = 5242880; // 5MB
    
    public function validate(UploadedFile $file)
    {
        // Non-breaking validation
        $errors = [];
        
        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedExtensions);
        }
        
        // Check size
        if ($file->getSize() > $this->maxSize) {
            $errors[] = 'File size exceeds maximum allowed size of 5MB';
        }
        
        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/jpg',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Invalid file type detected';
        }
        
        return $errors;
    }
    
    public function sanitizeFilename($filename)
    {
        // Remove any path components
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Ensure unique filename
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        return $name . '_' . uniqid() . '.' . $extension;
    }
}

// Usage in controller (non-breaking)
public function upload(Request $request)
{
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $uploader = new SecureFileUpload();
        
        // Validate
        $errors = $uploader->validate($file);
        if (!empty($errors)) {
            return back()->withErrors($errors);
        }
        
        // Sanitize filename
        $filename = $uploader->sanitizeFilename($file->getClientOriginalName());
        
        // Store securely
        $path = $file->storeAs('uploads', $filename, 'local');
        
        // Continue with existing logic...
    }
}
```

### Step 9: Add Password Strength Validation (Non-Breaking)

```php
// app/Rules/StrongPassword.php
<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    protected $message = '';
    
    public function passes($attribute, $value)
    {
        $errors = [];
        
        if (strlen($value) < 8) {
            $errors[] = 'at least 8 characters';
        }
        
        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = 'one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = 'one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = 'one number';
        }
        
        if (!preg_match('/[@$!%*?&#]/', $value)) {
            $errors[] = 'one special character (@$!%*?&#)';
        }
        
        if (!empty($errors)) {
            $this->message = 'Password must contain ' . implode(', ', $errors);
            return false;
        }
        
        return true;
    }
    
    public function message()
    {
        return $this->message ?: 'Password does not meet security requirements';
    }
}

// Apply to registration/password change without breaking existing users
// app/Http/Controllers/Auth/RegisteredUserController.php
public function store(Request $request)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', new StrongPassword()], // Add rule
    ]);
    
    // ... existing registration logic
}
```

### Step 10: Database Query Security Layer (Non-Breaking)

```php
// app/Traits/SecureQueries.php
<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait SecureQueries
{
    /**
     * Secure query wrapper that prevents SQL injection
     */
    protected function secureWhere($query, $column, $operator, $value = null)
    {
        // Handle two-argument syntax
        if (func_num_args() === 3) {
            $value = $operator;
            $operator = '=';
        }
        
        // Whitelist operators
        $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', 'like', 'not like', 'in', 'not in'];
        
        if (!in_array(strtolower($operator), $allowedOperators)) {
            throw new \InvalidArgumentException('Invalid operator');
        }
        
        // Validate column name (alphanumeric, underscore, dot for relations)
        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $column)) {
            throw new \InvalidArgumentException('Invalid column name');
        }
        
        return $query->where($column, $operator, $value);
    }
    
    /**
     * Secure raw query execution
     */
    protected function secureRaw($sql, array $bindings = [])
    {
        // Check for common SQL injection patterns
        $dangerousPatterns = [
            '/union\s+select/i',
            '/insert\s+into/i',
            '/drop\s+table/i',
            '/update\s+set/i',
            '/delete\s+from/i',
            '/exec\s*\(/i',
            '/execute\s*\(/i',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                \Log::channel('security')->critical('Potential SQL injection attempt', [
                    'sql' => $sql,
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);
                
                throw new \Exception('Invalid query detected');
            }
        }
        
        return DB::select($sql, $bindings);
    }
}

// Use in controllers
use App\Traits\SecureQueries;

class AssessmentController extends Controller
{
    use SecureQueries;
    
    public function search(Request $request)
    {
        $query = Assessment::query();
        
        // Use secure where instead of raw where
        if ($request->has('category')) {
            $query = $this->secureWhere($query, 'category', '=', $request->category);
        }
        
        return $query->get();
    }
}
```

## Python RAG Service Security (Non-Breaking)

```python
# python-rag/secure_config.py
import os
from pathlib import Path
from dotenv import load_dotenv

# Load environment variables
env_path = Path('.') / '.env'
load_dotenv(dotenv_path=env_path)

class SecureConfig:
    # API Keys from environment
    OPENROUTER_API_KEY = os.getenv('OPENROUTER_API_KEY')
    
    # Security settings
    MAX_PROMPT_LENGTH = 1000
    MAX_CONTEXT_LENGTH = 5000
    RATE_LIMIT_PER_MINUTE = 10
    
    # Validate required settings
    @classmethod
    def validate(cls):
        if not cls.OPENROUTER_API_KEY:
            raise ValueError("OPENROUTER_API_KEY not configured in .env file")
        
        # Don't log the actual key
        print(f"API Key configured: ***{cls.OPENROUTER_API_KEY[-4:]}")

# python-rag/secure_rag_service.py
from secure_config import SecureConfig
import re
import hashlib
from functools import wraps
from datetime import datetime, timedelta

class SecurityMiddleware:
    def __init__(self):
        self.request_history = {}
        SecureConfig.validate()
    
    def rate_limit(self, user_id: str) -> bool:
        """Check if user has exceeded rate limit"""
        now = datetime.now()
        
        # Clean old entries
        self.request_history = {
            k: v for k, v in self.request_history.items()
            if now - v[-1] < timedelta(minutes=1)
        }
        
        # Check user's requests
        if user_id in self.request_history:
            recent_requests = [
                t for t in self.request_history[user_id]
                if now - t < timedelta(minutes=1)
            ]
            
            if len(recent_requests) >= SecureConfig.RATE_LIMIT_PER_MINUTE:
                return False
            
            self.request_history[user_id] = recent_requests + [now]
        else:
            self.request_history[user_id] = [now]
        
        return True
    
    def sanitize_prompt(self, prompt: str) -> str:
        """Remove potentially dangerous content"""
        # Truncate to max length
        prompt = prompt[:SecureConfig.MAX_PROMPT_LENGTH]
        
        # Remove potential injection attempts
        dangerous_patterns = [
            r'system\s*:',
            r'assistant\s*:',
            r'ignore\s+previous',
            r'disregard\s+all',
        ]
        
        for pattern in dangerous_patterns:
            prompt = re.sub(pattern, '', prompt, flags=re.IGNORECASE)
        
        return prompt.strip()

# Apply to FastAPI routes
from fastapi import HTTPException, Request

security = SecurityMiddleware()

@app.post("/chat")
async def secure_chat(request: Request, query: str, user_id: str):
    # Rate limiting
    if not security.rate_limit(user_id):
        raise HTTPException(status_code=429, detail="Rate limit exceeded")
    
    # Sanitize input
    safe_query = security.sanitize_prompt(query)
    
    # Process with existing RAG logic
    # ... your existing code ...
```

## Testing Security Without Breaking

### 1. Security Test Script
```bash
#!/bin/bash
# security-test.sh

echo "Running non-breaking security tests..."

# Test 1: Check for exposed secrets
echo "Checking for exposed secrets..."
grep -r "password\|secret\|key\|token" --include="*.php" --include="*.js" --exclude-dir=vendor --exclude-dir=node_modules . | grep -v "//\|#\|\*"

# Test 2: Check file permissions
echo "Checking file permissions..."
find . -type f -name "*.php" -exec ls -la {} \; | grep -E "^-rw-rw-rw-|^-rwxrwxrwx"

# Test 3: Check for debug mode
echo "Checking debug mode..."
grep "APP_DEBUG=true" .env

# Test 4: Test rate limiting
echo "Testing rate limiting..."
for i in {1..10}; do
    curl -X POST http://localhost:8000/login \
        -H "Content-Type: application/json" \
        -d '{"email":"test@test.com","password":"test"}' \
        -w "\nAttempt $i: HTTP %{http_code}\n"
done

echo "Security tests complete!"
```

### 2. Monitoring Script
```php
// app/Console/Commands/SecurityMonitor.php
<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SecurityMonitor extends Command
{
    protected $signature = 'security:monitor';
    protected $description = 'Monitor security events';
    
    public function handle()
    {
        $this->info('Starting security monitoring...');
        
        // Check failed login attempts
        $failedLogins = DB::table('failed_logins')
            ->where('created_at', '>=', now()->subHour())
            ->count();
        
        if ($failedLogins > 10) {
            $this->error("High number of failed logins: {$failedLogins}");
            Log::channel('security')->critical('High failed login rate detected');
        }
        
        // Check for suspicious queries
        $logs = file_get_contents(storage_path('logs/laravel.log'));
        if (preg_match('/sql injection|xss|csrf/i', $logs)) {
            $this->error('Potential security threat detected in logs');
        }
        
        // Check session anomalies
        $sessions = DB::table('sessions')
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->having('count', '>', 5)
            ->get();
        
        if ($sessions->isNotEmpty()) {
            $this->warn('Users with multiple sessions detected');
        }
        
        $this->info('Security monitoring complete');
    }
}

// Run periodically via cron
// * * * * * cd /path-to-project && php artisan security:monitor >> /dev/null 2>&1
```

## Deployment Security Checklist

### Pre-Deployment
- [ ] Change default passwords
- [ ] Update .env with production values
- [ ] Set APP_DEBUG=false
- [ ] Set APP_ENV=production
- [ ] Generate new APP_KEY
- [ ] Configure HTTPS
- [ ] Set secure session settings
- [ ] Enable CSRF protection
- [ ] Configure rate limiting
- [ ] Set up monitoring

### Post-Deployment
- [ ] Verify security headers
- [ ] Test rate limiting
- [ ] Check error handling
- [ ] Verify logging works
- [ ] Test session timeout
- [ ] Verify HTTPS redirect
- [ ] Check file permissions
- [ ] Monitor first 24 hours
- [ ] Review security logs
- [ ] Document any issues

## Rollback Plan

If any security measure causes issues:

```bash
# 1. Disable specific middleware
# Comment out in app/Http/Kernel.php

# 2. Clear all caches
php artisan optimize:clear

# 3. Restore previous configuration
git checkout -- config/

# 4. Restart services
php artisan queue:restart
sudo service nginx restart

# 5. Monitor logs
tail -f storage/logs/laravel.log
```

All security implementations are designed to be non-breaking and can be disabled individually without affecting core functionality.
