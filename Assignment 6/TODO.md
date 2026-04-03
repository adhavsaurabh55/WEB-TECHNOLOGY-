# Employee Management System - Setup & Run Guide

## Prerequisites
- **XAMPP** installed and running (Apache + MySQL)
  - MySQL port set to **3307** (as used in code)
  - Apache serving PHP files

## 1. Setup Database (phpMyAdmin)
1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Login as `root` (default: no password)
3. Click **SQL** tab
4. Copy-paste contents of `setup.sql`
5. Click **Go** to create `employee_db` + `employees` table + sample data

## 2. Run the Application
1. Ensure **XAMPP Apache** is running
2. Open `index.php` in browser:
   ```
   http://localhost/path/to/Assignment 6/index.php
   ```
3. Or use VSCode **Live Server** / **PHP Server** extension

## Features ✅
- ➕ **Add** new employee records
- ✏️ **Edit** existing records (ID/Name fixed)
- 🗑️ **Delete** records by ID
- 📋 **Display** all records in styled table

## Test Flow
1. Click **Display** → See sample data (EMP001)
2. Fill form → **Add** new employee (e.g. EMP002)
3. Fill same ID → **Edit** fields
4. Fill ID → **Delete**
5. **Display** to verify

## Troubleshooting
- **DB Connection Error**: Check XAMPP MySQL port=3307
- **No Records**: Run setup.sql again
- **Styling Issues**: Ensure internet for Google Fonts

## File Structure
```
├── index.php      # Main app (UI + Logic)
├── setup.sql      # Database schema + sample data
├── process.php    # Unused (ignore/delete)
└── TODO.md        # This file
```

**Ready to use! 🚀**
