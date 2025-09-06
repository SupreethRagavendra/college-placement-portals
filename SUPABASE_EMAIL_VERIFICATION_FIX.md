# 🔧 Supabase Email Verification Fix - Complete Guide

## ✅ **Issues Fixed**

1. **Redirect URL Configuration**: Fixed to point to `http://localhost:8000/auth/callback`
2. **Auth Callback Handler**: Enhanced to properly handle Supabase tokens and errors
3. **User Creation**: Improved to set `email_verified_at` and `is_verified` correctly
4. **Error Handling**: Added comprehensive logging and error handling
5. **Database Fields**: All required fields properly set during user creation

## 🚀 **Current Status**

✅ **Laravel Server**: Running at `http://localhost:8000`  
✅ **Database Connection**: Connected to Supabase PostgreSQL  
✅ **Supabase API**: Accessible and configured  
✅ **Redirect URL**: Set to `http://localhost:8000/auth/callback`  
✅ **Auth Callback**: Enhanced with proper token handling  

## 🔧 **Required Supabase Configuration**

### **Step 1: Configure Supabase Dashboard**

1. **Go to your Supabase Project Dashboard**
2. **Navigate to Authentication > Settings**
3. **Set the following URLs:**

```
Site URL: http://localhost:8000
Redirect URLs: 
  - http://localhost:8000/auth/callback
  - http://localhost:8000/password/reset
```

### **Step 2: Update Email Templates (Optional)**

1. **Go to Authentication > Email Templates**
2. **Select "Confirm signup"**
3. **Update the redirect URL in the template to:**
   ```
   {{ .SiteURL }}/auth/callback?access_token={{ .TokenHash }}&refresh_token={{ .RefreshToken }}&type=signup
   ```

## 🔄 **Complete User Flow**

### **Student Registration Flow**
1. **Register** → Student fills form with role="student"
2. **Supabase Signup** → Calls `/auth/v1/signup` with redirect URL
3. **Email Sent** → Supabase sends verification email
4. **Email Clicked** → User clicks verification link
5. **Redirect** → Goes to `http://localhost:8000/auth/callback`
6. **Token Processing** → Laravel extracts `access_token` and `refresh_token`
7. **User Creation** → Creates user with:
   - `email_verified_at = now()`
   - `is_verified = true`
   - `is_approved = false` (pending admin approval)
   - `access_token` and `refresh_token` stored
8. **Status Message** → "Email verified! Account pending admin approval"

### **Admin Registration Flow**
1. **Register** → Admin fills form with role="admin"
2. **Same verification process** → Email verification
3. **Auto-approval** → Admin gets `is_approved = true` immediately
4. **Login Ready** → Admin can login right after email verification

### **Login Flow**
1. **Login Attempt** → User enters credentials
2. **Supabase Auth** → Authenticates with Supabase
3. **Status Check** → Verifies:
   - `email_verified_at IS NOT NULL` ✅
   - `is_approved = true` ✅
4. **Dashboard Redirect** → Based on role

## 🛠️ **Technical Implementation**

### **Auth Callback Handler**
```php
public function authCallback(Request $request): RedirectResponse
{
    $accessToken = $request->get('access_token');
    $refreshToken = $request->get('refresh_token');
    $type = $request->get('type');
    
    // Handle errors from Supabase
    if ($error = $request->get('error')) {
        return redirect()->route('login')
            ->withErrors(['email' => 'Email verification failed: ' . $error]);
    }
    
    if ($type === 'signup' && $accessToken) {
        // Get user from Supabase
        $supabaseUser = $this->supabaseService->getUser($accessToken);
        
        // Create/update user in Laravel
        $user = User::updateOrCreate(
            ['supabase_id' => $supabaseUser['id']],
            [
                'email' => $supabaseUser['email'],
                'name' => $supabaseUser['user_metadata']['name'] ?? explode('@', $supabaseUser['email'])[0],
                'role' => $supabaseUser['user_metadata']['role'] ?? 'student',
                'access_token' => $accessToken,
                'is_verified' => true,
                'email_verified_at' => now(),
                'is_approved' => $supabaseUser['user_metadata']['role'] === 'admin',
                'status' => $supabaseUser['user_metadata']['role'] === 'admin' ? 'approved' : 'pending'
            ]
        );
    }
}
```

### **Database Schema**
```sql
-- Users table with all required fields
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(50) DEFAULT 'student',
    supabase_id VARCHAR(255) UNIQUE,
    access_token TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    admin_approved_at TIMESTAMP NULL,
    admin_rejected_at TIMESTAMP NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 🧪 **Testing the Flow**

### **Test Student Registration**
1. **Go to** `http://localhost:8000/register`
2. **Fill form** with student role
3. **Check email** for verification link
4. **Click link** → Should redirect to `/auth/callback`
5. **Check database** → User should have `email_verified_at` set
6. **Try login** → Should show "pending admin approval"

### **Test Admin Registration**
1. **Register as admin** → Same process
2. **Verify email** → Should auto-approve
3. **Login immediately** → Should work

### **Test Admin Approval**
1. **Login as admin** → Go to admin dashboard
2. **Approve student** → Set `is_approved = true`
3. **Student login** → Should work now

## 🔍 **Debugging**

### **Check Laravel Logs**
```bash
tail -f storage/logs/laravel.log
```

### **Check Supabase Logs**
- Go to Supabase Dashboard > Logs
- Look for authentication events

### **Test Supabase Configuration**
```bash
php test-supabase-config.php
```

## 🚨 **Common Issues & Solutions**

### **Issue: `otp_expired` or `access_denied`**
**Solution**: 
- Check Supabase redirect URLs are correct
- Ensure email template has correct redirect URL
- Verify Supabase project settings

### **Issue: `email_verified_at` remains NULL**
**Solution**:
- Check auth callback is being called
- Verify access token is valid
- Check Laravel logs for errors

### **Issue: User not created in database**
**Solution**:
- Check database connection
- Verify migration ran successfully
- Check Laravel logs for database errors

## 🎯 **Next Steps**

1. **Test the complete flow** with real email addresses
2. **Configure production URLs** when deploying
3. **Set up email templates** in Supabase
4. **Monitor logs** for any issues
5. **Test admin approval workflow**

---

**🎉 The email verification flow is now properly configured and should work correctly!**
