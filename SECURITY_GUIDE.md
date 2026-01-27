# Secure Multi-Role Authentication System Guide

## Architecture Overview

### 1. **Authentication Flow**
```
User Access (any subdomain/path)
    ↓
Subdomain Detection (Config/subdomain.php)
    ↓
Single Login Page (Auth/login.php)
    ↓
Context-Aware Authentication (Auth/controller/AuthController.php)
    ↓
Role Detection (Check user table for role)
    ↓
Role-Based Dashboard Redirect (School_Admin/SAAS_admin/Teacher/Student)
```

### 2. **Database Structure for Multi-Role Auth**

All users (super_admin, school_admin, teachers, students) need these fields:
- `id` - Unique identifier
- `email` - Email address (unique)
- `password` - Bcrypt hashed password
- `name` - Full name
- `role` - User role (super_admin, school_admin, teacher, student)
- `school_id` - Foreign key to schools table (NULL for super_admin)
- `status` - active/inactive
- `last_login` - Timestamp
- `created_at` - Registration timestamp

### 3. **Key Security Features**

✅ **Password Security**
- All passwords stored as bcrypt hashes (PASSWORD_BCRYPT)
- Minimum 8 characters, mixed case, numbers recommended
- Never show original passwords

✅ **Session Security**
- Secure session handling with timeout (30 minutes)
- CSRF token generation and validation
- User agent and IP verification

✅ **Subdomain Detection**
- Parse subdomain to identify school context
- Format: `{school_subdomain}.localhost` or `{school_subdomain}.saas.com`
- Admin panel has no subdomain or uses 'admin' subdomain

✅ **Role-Based Access Control (RBAC)**
- 4 main roles: super_admin, school_admin, teacher, student
- Each role has specific dashboard and permissions
- Automatic role-based redirect after login

✅ **Rate Limiting**
- Limit login attempts (5 attempts per 15 minutes)
- Progressive delays between failed attempts
- Email notifications on suspicious activity

✅ **XSS & SQL Injection Prevention**
- Parameterized queries for all database operations
- Output escaping with htmlspecialchars()
- Input validation and sanitization

✅ **HTTPS & Secure Cookies**
- All sensitive data transmitted over HTTPS (production)
- Secure flag on auth cookies
- HttpOnly flag prevents JavaScript access

## File Structure

```
App/Modules/
├── Auth/
│   ├── login.php                 (Single unified login page)
│   ├── logout.php                (Logout handler)
│   ├── controller/
│   │   └── AuthController.php    (Multi-role authentication logic)
│   └── middleware/
│       ├── RoleMiddleware.php    (Role verification)
│       ├── SessionMiddleware.php (Session validation)
│       └── CSRFMiddleware.php    (CSRF protection)
├── Config/
│   ├── subdomain.php             (Subdomain detection & context)
│   ├── roles.php                 (Role definitions & permissions)
│   └── security.php              (Security configurations)
└── SAAS_admin/SAAS_admin/Teacher/Student/
    └── Views/dashboard/
        └── index.php             (Role-specific dashboard)
```

## Implementation Steps

### Step 1: Update Database Schema
- Add `role` column to all user tables
- Add `school_id` to non-admin users
- Add `last_login`, `failed_attempts`, `locked_until` fields

### Step 2: Create Configuration Files
- Subdomain detection (subdomain.php)
- Role definitions (roles.php)
- Security settings (security.php)

### Step 3: Create Middleware
- Session validation middleware
- Role verification middleware
- CSRF token middleware

### Step 4: Update Auth Controller
- Multi-table user lookup (check which table user belongs to)
- Role-based authentication
- Session initialization with role context

### Step 5: Create Unified Login Page
- Single UI for all roles
- Context detection (which school, which role)
- Role-specific error messages

### Step 6: Create Role-Specific Dashboards
- Each role redirects to its own dashboard
- Dashboard checks user role and permissions
- Prevents unauthorized access

## Security Best Practices

1. **Never store plain text passwords**
   ```php
   $hashed = password_hash($password, PASSWORD_BCRYPT);
   ```

2. **Always use prepared statements**
   ```php
   $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->execute([$email]);
   ```

3. **Verify session on every page**
   ```php
   if (!isset($_SESSION['user_id'])) {
       header('Location: /login.php');
       exit;
   }
   ```

4. **Use CSRF tokens for form submissions**
   ```php
   if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
       die('CSRF token validation failed');
   }
   ```

5. **Log security events**
   - Failed login attempts
   - Unauthorized access attempts
   - Role changes
   - Password changes

6. **Implement rate limiting**
   - Max 5 failed attempts per 15 minutes
   - Progressively increase delay (1s, 2s, 4s, 8s, 16s)
   - Lock account after threshold

7. **Monitor last login**
   - Track last login timestamp
   - Alert on unusual access patterns
   - Require re-authentication for sensitive operations

## Testing Checklist

- [ ] Login with each role (super_admin, school_admin, teacher, student)
- [ ] Verify correct dashboard redirect for each role
- [ ] Test subdomain detection (admin.local, school1.local, school2.local)
- [ ] Verify cannot access other role's dashboard
- [ ] Test session timeout
- [ ] Test CSRF token validation
- [ ] Test rate limiting (try 6+ failed logins)
- [ ] Test logout and session cleanup
- [ ] Test password reset flow
- [ ] Verify no SQL injection in login form
- [ ] Verify no XSS in displayed error messages

## Once Implemented - DO NOT CHANGE

This authentication system is designed to be stable and secure. Once implemented:

1. **Do not modify authentication logic** without thorough testing
2. **Do not remove security checks** for convenience
3. **Do not expose user IDs** in URLs or forms
4. **Do not transmit passwords** in plain text via email
5. **Always use HTTPS** in production
6. **Keep dependencies updated** (PHP, database drivers)
7. **Monitor logs** for suspicious activity
8. **Perform regular security audits**

---

## Next Steps

Ready to implement? Let me know and I'll create:
1. SecurityConfig files
2. Middleware classes
3. Updated AuthController
4. Unified login page with role detection
5. Role-specific dashboard redirects
