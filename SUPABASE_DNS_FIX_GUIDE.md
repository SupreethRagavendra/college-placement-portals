# Supabase DNS Fix - Complete Guide

## 🔴 THE PROBLEM

Your error:
```
SQLSTATE[08006] [7] could not translate host name "db.wkqbukidxmzbgwauncrl.supabase.co" 
to address: Unknown host
```

**Root Cause:**
- `nslookup` can resolve the hostname ✅
- BUT PHP cannot resolve it ❌
- This is a Windows DNS configuration issue where applications can't use DNS properly
- Supabase only provides IPv6 address, which may not be supported by PHP on your system

---

## ✅ THE SOLUTION

### **Run this script AS ADMINISTRATOR:**

```
FIX_HOSTS_FILE_NOW.ps1
```

**How to run:**
1. Right-click on `FIX_HOSTS_FILE_NOW.ps1`
2. Select **"Run with PowerShell"**
3. Click **"Yes"** when Windows asks for Administrator permission
4. Wait for it to complete
5. Check if the database test passes

---

## 📋 WHAT THIS DOES

1. **Backs up your hosts file** to `C:\Windows\System32\drivers\etc\hosts.backup_TIMESTAMP`
2. **Adds this line to hosts file:**
   ```
   2406:da18:243:740a:6d30:33f9:31ae:d2fa    db.wkqbukidxmzbgwauncrl.supabase.co
   ```
3. **Flushes DNS cache**
4. **Tests the database connection** from PHP
5. **Shows you the results**

---

## 🎯 STEP-BY-STEP INSTRUCTIONS

### Step 1: Run the Fix Script
```
Right-click FIX_HOSTS_FILE_NOW.ps1 → Run with PowerShell → Click "Yes"
```

### Step 2: Check the Test Results
The script will automatically test the connection. You should see:
```
✅ SUCCESS: Resolved to IP: 2406:da18:243:740a:6d30:33f9:31ae:d2fa
✅ SUCCESS: Connected to database!
```

### Step 3: Clear Laravel Caches
```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Restart Your Server
Stop (Ctrl+C) and restart:
```bash
php artisan serve
```

### Step 5: Try Logging In
1. Go to: http://localhost:8000/login
2. Enter your credentials
3. Should work! ✅

---

## 🚨 IF IT STILL DOESN'T WORK

### Issue: PHP doesn't support IPv6

If after running the script you still see:
```
❌ FAILED: Cannot resolve hostname
```

**This means your PHP installation doesn't support IPv6.**

### Solution A: Check IPv6 Support
Run this command:
```bash
php -r "echo (defined('AF_INET6') ? 'IPv6 Supported' : 'IPv6 NOT Supported');"
```

### Solution B: Enable IPv6 in Windows
1. Open **Control Panel** → **Network and Sharing Center**
2. Click your network connection
3. Click **Properties**
4. Make sure **"Internet Protocol Version 6 (TCP/IPv6)"** is checked ✅
5. Click OK
6. **Restart your computer**
7. Run the fix script again

### Solution C: Ask Your Network Admin
If you're on a corporate network:
- IPv6 might be blocked
- DNS resolution might be restricted
- Contact your IT department

---

## 🔄 ALTERNATIVE: Use VPN or Change Network

Sometimes corporate/university networks block certain DNS resolutions:

1. **Try a different network** (mobile hotspot, home WiFi)
2. **Disable VPN** if you're using one
3. **Try on another computer** to verify it's not a network issue

---

## 📝 TECHNICAL DETAILS

### Why This Happens

**Windows has multiple DNS resolution systems:**

1. **Command-line tools** (nslookup, ping) → Uses Windows DNS directly ✅
2. **Applications** (PHP, Node, Python) → Uses getaddrinfo() system call ❌

When PHP calls `gethostbyname()`, it uses the system's `getaddrinfo()` function, which is different from what `nslookup` uses.

### The Hosts File Bypass

The **hosts file** (`C:\Windows\System32\drivers\etc\hosts`) is checked BEFORE DNS, by ALL applications including PHP.

By adding:
```
2406:da18:243:740a:6d30:33f9:31ae:d2fa    db.wkqbukidxmzbgwauncrl.supabase.co
```

We bypass DNS entirely and tell the system "this hostname = this IP address".

---

## 🧪 TESTING

### Test 1: Check Hosts File
```powershell
Get-Content C:\Windows\System32\drivers\etc\hosts | Select-String "supabase"
```

Should show:
```
2406:da18:243:740a:6d30:33f9:31ae:d2fa    db.wkqbukidxmzbgwauncrl.supabase.co
```

### Test 2: Test from PHP
```bash
php test-db-connection.php
```

Should show:
```
✅ SUCCESS: Connected to database!
```

### Test 3: Laravel Connection
```bash
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected!';"
```

---

## 🗑️ REVERTING THE CHANGES

If you want to remove the hosts file entry:

### Option 1: Automated
```powershell
# Run as Administrator
$hostsFile = "C:\Windows\System32\drivers\etc\hosts"
$content = Get-Content $hostsFile | Where-Object { $_ -notmatch "supabase" }
$content | Set-Content $hostsFile
ipconfig /flushdns
```

### Option 2: Manual
1. Open Notepad **as Administrator**
2. Open: `C:\Windows\System32\drivers\etc\hosts`
3. Delete the line with `supabase`
4. Save
5. Run: `ipconfig /flushdns`

### Option 3: Restore Backup
```powershell
# List backups
Get-ChildItem C:\Windows\System32\drivers\etc\hosts.backup_*

# Restore (choose the one you want)
Copy-Item C:\Windows\System32\drivers\etc\hosts.backup_YYYYMMDD_HHMMSS C:\Windows\System32\drivers\etc\hosts -Force
```

---

## 📞 SUPPORT

If none of this works:

### Check These:
- [ ] Are you using a VPN? (Disable it)
- [ ] Are you on corporate/university network? (Try different network)
- [ ] Is IPv6 enabled in Windows network settings?
- [ ] Did you run the script as Administrator?
- [ ] Did you restart the development server after the fix?
- [ ] Did you clear browser cache?

### System Information to Provide:
```powershell
php -v
php -m | Select-String pgsql
ipconfig /all
Get-Content C:\Windows\System32\drivers\etc\hosts | Select-String supabase
```

---

## ✅ SUCCESS CHECKLIST

After running the fix:

- [ ] Script ran without errors
- [ ] `test-db-connection.php` shows success
- [ ] `php artisan serve` starts without errors
- [ ] Login page loads at http://localhost:8000/login
- [ ] Can login successfully
- [ ] No DNS errors in terminal
- [ ] Session persists after refresh

---

**Last Updated:** 2025-10-05  
**Issue:** PHP cannot resolve Supabase hostname  
**Solution:** Add IPv6 address to Windows hosts file  
**Status:** Ready to apply

🎉 **Run `FIX_HOSTS_FILE_NOW.ps1` as Administrator and you should be good to go!**

