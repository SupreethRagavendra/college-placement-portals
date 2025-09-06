# Supabase Integration Setup Guide

## 🚀 Complete Laravel + Supabase College Placement Portal

This guide will help you set up the complete Supabase integration for your College Placement Training Portal.

## 📋 Prerequisites

1. **Supabase Project**: You need a Supabase project with the following:
   - Project URL: `https://wkqbukidxmzbgwauncrl.supabase.co`
   - Database: PostgreSQL
   - Authentication enabled

2. **Supabase API Keys**:
   - Anonymous Key (anon key)
   - Service Role Key (for admin operations)

## 🔧 Environment Configuration

### 1. Update your `.env` file with Supabase credentials:

```env
# Supabase Database Configuration
DB_CONNECTION=pgsql
DB_HOST=db.wkqbukidxmzbgwauncrl.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD="Supreeeth24#"
DB_SSLMODE=require

# Supabase Configuration
SUPABASE_URL=https://wkqbukidxmzbgwauncrl.supabase.co
SUPABASE_ANON_KEY=your_supabase_anon_key_here
SUPABASE_SERVICE_ROLE_KEY=your_supabase_service_role_key_here
```

### 2. Get your Supabase API Keys:

1. Go to your Supabase project dashboard
2. Navigate to **Settings** → **API**
3. Copy the following keys:
   - **anon public** key → `SUPABASE_ANON_KEY`
   - **service_role** key → `SUPABASE_SERVICE_ROLE_KEY`

## 🏗️ Database Setup

### 1. Run Migrations:
```bash
php artisan migrate
```

### 2. Create Admin User:
```bash
php artisan db:seed --class=AdminSeeder
```

## 🔐 Authentication Flow

### Student Registration Process:
1. **Step 1**: Student registers → Supabase sends email verification
2. **Step 2**: Student clicks email verification link → Email verified
3. **Step 3**: Admin reviews and approves/rejects student
4. **Step 4**: Only approved students can login

### Admin Registration Process:
1. **Step 1**: Admin registers → Supabase sends email verification
2. **Step 2**: Admin clicks email verification link → Can login immediately

## 📁 File Structure

```
app/
├── Http/Controllers/
│   ├── SupabaseAuthController.php    # Handles Supabase authentication
│   └── AdminController.php           # Admin student management
├── Models/
│   └── User.php                      # Updated with Supabase fields
├── Services/
│   └── SupabaseService.php           # Supabase API service
└── config/
    └── supabase.php                  # Supabase configuration

resources/views/
├── auth/
│   ├── login.blade.php               # Login form
│   ├── register.blade.php            # Registration form
│   ├── forgot-password.blade.php     # Password reset
│   └── verify.blade.php              # Email verification notice
└── admin/
    └── dashboard.blade.php           # Admin dashboard with student management
```

## 🎯 Key Features Implemented

### ✅ Supabase Authentication
- User registration with email verification
- Login with Supabase Auth APIs
- Password reset functionality
- Secure token-based authentication

### ✅ Two-Step Student Verification
- **Step 1**: Email verification via Supabase
- **Step 2**: Admin approval system
- Automatic cleanup of rejected students

### ✅ Admin Panel
- Student approval/rejection system
- Dashboard with statistics
- Bulk operations
- Real-time student management

### ✅ Security Features
- CSRF protection
- Role-based access control
- Secure API communication
- Admin details hidden from frontend

## 🚀 Getting Started

### 1. Configure Environment:
```bash
# Copy the environment template
cp .env.example .env

# Update with your Supabase credentials
# Edit .env file with your Supabase keys
```

### 2. Install Dependencies:
```bash
composer install
```

### 3. Run Setup:
```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Create admin user
php artisan db:seed --class=AdminSeeder
```

### 4. Start Development Server:
```bash
php artisan serve
```

## 🧪 Testing the Integration

### Test Student Registration:
1. Go to `http://127.0.0.1:8000/register`
2. Select "Student" role
3. Fill in details and submit
4. Check email for verification link
5. Click verification link
6. Try to login (should show "pending approval" message)

### Test Admin Registration:
1. Go to `http://127.0.0.1:8000/register`
2. Select "Admin" role
3. Fill in details and submit
4. Check email for verification link
5. Click verification link
6. Login should work immediately

### Test Admin Panel:
1. Login as admin
2. Go to admin dashboard
3. See pending students
4. Approve/reject students
5. Check statistics update

## 🔧 API Endpoints

### Authentication Routes:
- `POST /register` - User registration
- `POST /login` - User login
- `POST /logout` - User logout
- `POST /forgot-password` - Password reset request
- `GET /verify-email` - Email verification callback

### Admin Routes:
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/pending-students` - Pending students list
- `POST /admin/approve-student/{id}` - Approve student
- `POST /admin/reject-student/{id}` - Reject student
- `POST /admin/bulk-approve` - Bulk approve students

## 🛠️ Troubleshooting

### Common Issues:

1. **"Connection failed" error**:
   - Check Supabase database credentials
   - Ensure SSL is enabled (`DB_SSLMODE=require`)

2. **"Invalid API key" error**:
   - Verify `SUPABASE_ANON_KEY` and `SUPABASE_SERVICE_ROLE_KEY`
   - Check if keys are correctly copied from Supabase dashboard

3. **Email verification not working**:
   - Check Supabase email settings
   - Verify email templates are configured
   - Check spam folder

4. **Admin approval not working**:
   - Ensure user has `admin_approved_at` timestamp
   - Check user status in database

### Debug Mode:
Enable debug mode in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## 📞 Support

If you encounter any issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify Supabase project settings
3. Test database connection: `http://127.0.0.1:8000/test-db`

## 🎉 Success!

Once everything is configured, you'll have a fully functional College Placement Portal with:
- ✅ Supabase authentication
- ✅ Two-step student verification
- ✅ Admin approval system
- ✅ Secure role-based access
- ✅ Modern, responsive UI

Your portal is ready for production use!
