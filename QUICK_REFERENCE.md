# Quick Reference: Multi-Role Authentication

## Files Created

| File | Purpose | Status |
|------|---------|--------|
| `App/Config/SubdomainContext.php` | School detection via subdomain | ✅ Ready |
| `App/Config/RoleConfig.php` | Role definitions & permissions | ✅ Ready |
| `App/Config/SecurityConfig.php` | Security utilities & settings | ✅ Ready |
| `App/Config/Middleware/SessionMiddleware.php` | Session validation | ✅ Ready |
| `App/Config/Middleware/RoleMiddleware.php` | Role verification | ✅ Ready |
| `App/Config/Middleware/CSRFMiddleware.php` | CSRF protection | ✅ Ready |
| `App/Modules/Auth/login.php` | **NEEDS UPDATE** | ⏳ TODO |
| `App/Modules/Auth/logout.php` | **NEEDS CREATION** | ⏳ TODO |
| `App/Modules/Auth/controller/AuthController.php` | **NEEDS UPDATE** | ⏳ TODO |

## Quick Usage

### On Every Protected Page
```php
<?php
require_once __DIR__ . '/../../../../Config/auth_check.php';

// Now you have:
// - $_SESSION with user data
// - $session (SessionMiddleware)
// - $role (RoleMiddleware)
?>
```

### Check Role
```php
$role->requireRole('school_admin');          // Must be school_admin
$role->requireAdmin();                        // Must be admin
$role->requirePermission('manage_schools');  // Must have permission
$role->hasRole('teacher');                   // Check if teacher
$role->isAdmin();                             // Check if admin role
```

### Get User Info
```php
$session->getSessionInfo();  // Returns array with all session data
$_SESSION['user_id'];        // Current user ID
$_SESSION['role'];           // Current user's role
$_SESSION['school_id'];      // User's school (if applicable)
```

### Subdomain Context
```php
$context = SubdomainContext::getInstance($DB_con);
$context->getSubdomain();   // e.g., "school1"
$context->getSchoolId();    // e.g., 5
$context->isAdminAccess();  // true/false
$context->isSchoolAccess(); // true/false
```

### CSRF Protection
```php
// In form
<?php echo CSRFMiddleware::tokenInput(); ?>

// In processor
<?php CSRFMiddleware::verifyPostToken(); ?>

// In AJAX request
headers: {
    'X-CSRF-TOKEN': getCookie('csrf_token')
}

// In AJAX processor
<?php CSRFMiddleware::verifyAjaxToken(); ?>
```

### Password Hashing
```php
// Hash password
$hash = SecurityConfig::hashPassword('MyPassword123');

// Verify password
$valid = SecurityConfig::verifyPassword('MyPassword123', $hash);

// Validate strength
$result = SecurityConfig::validatePasswordStrength('Password');
// Returns: ['valid' => true/false, 'errors' => [...]]
```

## Database Changes Needed

```sql
-- Add columns to super_admin
ALTER TABLE super_admin 
ADD COLUMN role VARCHAR(50) DEFAULT 'super_admin',
ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active',
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN failed_attempts INT DEFAULT 0,
ADD COLUMN locked_until TIMESTAMP NULL;

-- Create school_admin table
CREATE TABLE school_admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'school_admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    failed_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);

-- Add to teachers & students tables
ALTER TABLE teachers 
ADD COLUMN role VARCHAR(50) DEFAULT 'teacher',
ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active',
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN failed_attempts INT DEFAULT 0,
ADD COLUMN locked_until TIMESTAMP NULL;

ALTER TABLE students 
ADD COLUMN role VARCHAR(50) DEFAULT 'student',
ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active',
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN failed_attempts INT DEFAULT 0,
ADD COLUMN locked_until TIMESTAMP NULL;
```

## Roles & Permissions

### Super Admin
- Dashboard: `/School-SAAS/App/Modules/SAAS_admin/Views/dashboard/index.php`
- Permissions: manage_schools, manage_plans, manage_users, view_reports, manage_finance, manage_billing, view_system_logs

### School Admin
- Dashboard: `/School-SAAS/App/Modules/School_Admin/Views/index.php`
- Permissions: manage_teachers, manage_students, manage_courses, view_reports, manage_staff, view_billing

### Teacher
- Dashboard: `/School-SAAS/App/Modules/Teacher/Views/dashboard/index.php`
- Permissions: manage_classes, manage_attendance, manage_grades, view_students, upload_materials

### Student
- Dashboard: `/School-SAAS/App/Modules/Student/Views/dashboard/index.php`
- Permissions: view_grades, view_attendance, view_materials, submit_assignments

## Subdomain Examples

```
http://localhost:8080/login.php → SAAS Admin panel
http://admin.school-saas.local/login.php → SAAS Admin panel
http://school1.school-saas.local/login.php → School1 Admin login
http://school2.school-saas.local/login.php → School2 Admin login
```

## Session Settings

- **Timeout**: 30 minutes
- **Security**: HttpOnly + Secure flags
- **CSRF Token**: 1 hour lifetime
- **Max Login Attempts**: 5 per 15 minutes
- **Hash Algorithm**: Bcrypt with cost 12

## Implementation Checklist

- [ ] Run SQL commands to add columns/create tables
- [ ] Update `AuthController.php` to check multiple tables
- [ ] Update `login.php` to use new security classes
- [ ] Create `logout.php` file
- [ ] Create `auth_check.php` helper
- [ ] Update all protected pages to use `auth_check.php`
- [ ] Test login for each role
- [ ] Test subdomain detection
- [ ] Test CSRF protection
- [ ] Test session timeout
- [ ] Deploy to production
- [ ] Set `SESSION_SECURE = true` in SecurityConfig.php
- [ ] Enable HTTPS

## Common Errors & Solutions

### "CSRF token validation failed"
- Make sure form includes `<?php echo CSRFMiddleware::tokenInput(); ?>`
- Ensure CSRF token in POST data matches session

### "Access Denied"
- User's role doesn't have required permission
- Check `RoleConfig.php` for permission definitions
- Verify user_role is set in session

### "Session expired"
- User inactive for 30 minutes
- Session destroyed on logout
- User needs to login again

### "Invalid school subdomain"
- School subdomain doesn't exist in database
- Check `schools` table for matching subdomain
- SubdomainContext logs errors

### Wrong dashboard after login
- Check `RoleConfig::getDashboard($role)`
- Verify role is correctly set in session
- Ensure dashboard file exists

---

## Support

See `SECURITY_GUIDE.md` and `AUTHENTICATION_IMPLEMENTATION_GUIDE.md` for detailed information.
