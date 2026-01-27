<?php
session_start();

// Include necessary files
require_once __DIR__ . '/../../Config/database.php';
require_once __DIR__ . '/../../Core/database.php';
require_once __DIR__ . '/../../Config/SubdomainContext.php';
require_once __DIR__ . '/../../Config/SecurityConfig.php';

// Get database connection
$db = Database::connect();

// Initialize subdomain context to detect school
$subdomainContext = SubdomainContext::getInstance($db);
$school = $subdomainContext->getContext();

// Check if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['school_id'])) {
        header('Location: /School-SAAS/App/Modules/School_Admin/Views/index.php');
    }
    exit;
}

// Check if there's a remembered email
$rememberedEmail = $_COOKIE['school_email'] ?? '';
$error = '';
$success = '';
$loginContext = 'Login';

// Detect login context
if ($school['is_admin']) {
    $loginContext = 'SAAS Admin Login';
} elseif ($school['school_id']) {
    $loginContext = 'School: ' . $school['school_name'];
} else if (empty($school['subdomain'])) {
    $loginContext = 'Admin Login';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        try {
            // Try to authenticate based on context
            $authenticated = false;
            $userRole = null;
            $userData = null;

            // Path 1: School-specific login (via subdomain)
            if ($school['school_id']) {
                // Authenticate against schools table using school_id + email
                $sql = "SELECT * FROM schools WHERE id = ? AND LOWER(email) = LOWER(?) LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([$school['school_id'], $email]);
                $schoolRecord = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($schoolRecord) {
                    // Verify password
                    $passwordValid = false;
                    $dbPassword = trim($schoolRecord['password']);
                    
                    // Check if password is hashed (bcrypt format)
                    if (preg_match('/^\$2[aby]\$/', $dbPassword)) {
                        // Password is hashed - use password_verify
                        $passwordValid = password_verify($password, $dbPassword);
                    } else {
                        // Password is plain text - compare directly
                        $passwordValid = (trim($password) === $dbPassword);
                        
                        // Hash it for future use
                        if ($passwordValid) {
                            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                            $updateSql = "UPDATE schools SET password = ? WHERE id = ?";
                            $updateStmt = $db->prepare($updateSql);
                            $updateStmt->execute([$hashedPassword, $schoolRecord['id']]);
                        }
                    }

                    if ($passwordValid) {
                        // Check if school status is active
                        if ($schoolRecord['status'] !== 'active') {
                            $error = 'Your school account is not active. Please contact the administrator.';
                        } else {
                            $authenticated = true;
                            $userRole = 'school_admin';
                            $userData = [
                                'id' => $schoolRecord['id'],
                                'school_id' => $schoolRecord['id'],
                                'school_name' => $schoolRecord['name'],
                                'email' => $schoolRecord['email'],
                                'subdomain' => $school['subdomain']
                            ];
                        }
                    } else {
                        $error = 'Invalid email or password';
                        // Log failed attempt with debug info
                        SecurityConfig::logSecurityEvent('school_login_failed', [
                            'email' => $email,
                            'reason' => 'invalid_password',
                            'db_password_hash' => (preg_match('/^\$2[aby]\$/', $dbPassword) ? 'hashed' : 'plain_text'),
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ]);
                    }
                } else {
                    $error = 'Invalid email or password';
                    // Log failed attempt
                    SecurityConfig::logSecurityEvent('school_login_failed', [
                        'email' => $email,
                        'reason' => 'email_not_found',
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                }
            }
            // Path 2: Super admin login (via admin.local or localhost)
            else if ($school['is_admin'] || empty($school['subdomain'])) {
                // Try to authenticate against super_admin table FIRST
                $sql = "SELECT * FROM super_admin WHERE LOWER(email) = LOWER(?) LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([$email]);
                $adminRecord = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($adminRecord) {
                    // Verify password
                    $passwordValid = false;
                    $dbPassword = trim($adminRecord['password']);
                    
                    // Check if password is hashed (bcrypt format)
                    if (preg_match('/^\$2[aby]\$/', $dbPassword)) {
                        // Password is hashed - use password_verify
                        $passwordValid = password_verify($password, $dbPassword);
                    } else {
                        // Password is plain text - compare directly
                        $passwordValid = (trim($password) === $dbPassword);
                        
                        // Hash it for future use
                        if ($passwordValid) {
                            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                            $updateSql = "UPDATE super_admin SET password = ? WHERE id = ?";
                            $updateStmt = $db->prepare($updateSql);
                            $updateStmt->execute([$hashedPassword, $adminRecord['id']]);
                        }
                    }

                    if ($passwordValid) {
                        $authenticated = true;
                        $userRole = 'super_admin';
                        $userData = [
                            'id' => $adminRecord['id'],
                            'name' => $adminRecord['name'] ?? 'Admin',
                            'email' => $adminRecord['email']
                        ];
                    } else {
                        $error = 'Invalid email or password';
                        // Log failed attempt with debug info
                        SecurityConfig::logSecurityEvent('admin_login_failed', [
                            'email' => $email,
                            'reason' => 'invalid_password',
                            'db_password_hash' => (preg_match('/^\$2[aby]\$/', $dbPassword) ? 'hashed' : 'plain_text'),
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ]);
                    }
                } else {
                    // Super admin not found, try to authenticate as school admin
                    // This allows school admins to login at localhost without subdomain
                    $schoolSql = "SELECT * FROM schools WHERE LOWER(email) = LOWER(?) LIMIT 1";
                    $schoolStmt = $db->prepare($schoolSql);
                    $schoolStmt->execute([$email]);
                    $schoolRecord = $schoolStmt->fetch(PDO::FETCH_ASSOC);

                    if ($schoolRecord) {
                        // Verify password
                        $passwordValid = false;
                        $dbPassword = trim($schoolRecord['password']);
                        
                        // Check if password is hashed (bcrypt format)
                        if (preg_match('/^\$2[aby]\$/', $dbPassword)) {
                            // Password is hashed - use password_verify
                            $passwordValid = password_verify($password, $dbPassword);
                        } else {
                            // Password is plain text - compare directly
                            $passwordValid = (trim($password) === $dbPassword);
                            
                            // Hash it for future use
                            if ($passwordValid) {
                                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                                $updateSql = "UPDATE schools SET password = ? WHERE id = ?";
                                $updateStmt = $db->prepare($updateSql);
                                $updateStmt->execute([$hashedPassword, $schoolRecord['id']]);
                            }
                        }

                        if ($passwordValid) {
                            // Check if school status is active
                            if ($schoolRecord['status'] !== 'active') {
                                $error = 'Your school account is not active. Please contact the administrator.';
                            } else {
                                $authenticated = true;
                                $userRole = 'school_admin';
                                $userData = [
                                    'id' => $schoolRecord['id'],
                                    'school_id' => $schoolRecord['id'],
                                    'school_name' => $schoolRecord['name'],
                                    'email' => $schoolRecord['email'],
                                    'subdomain' => $schoolRecord['subdomain'] ?? 'localhost'
                                ];
                            }
                        } else {
                            $error = 'Invalid email or password';
                            // Log failed attempt
                            SecurityConfig::logSecurityEvent('school_login_failed', [
                                'email' => $email,
                                'reason' => 'invalid_password',
                                'source' => 'localhost_fallback',
                                'ip' => $_SERVER['REMOTE_ADDR']
                            ]);
                        }
                    } else {
                        $error = 'Invalid email or password';
                        // Log failed attempt
                        SecurityConfig::logSecurityEvent('auth_login_failed', [
                            'email' => $email,
                            'reason' => 'email_not_found_in_any_table',
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ]);
                    }
                }
            } else {
                $error = 'Unable to determine login context. Please check your URL.';
            }

            // If authenticated, set session and redirect
            if ($authenticated && $userData) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['email'] = $userData['email'];
                $_SESSION['role'] = $userRole;
                $_SESSION['last_activity'] = time();

                // Add school-specific session data if available
                if ($userRole === 'school_admin') {
                    $_SESSION['school_id'] = $userData['school_id'];
                    $_SESSION['school_name'] = $userData['school_name'];
                    $_SESSION['subdomain'] = $userData['subdomain'];
                }

                // Log security event
                $eventType = ($userRole === 'super_admin') ? 'admin_login_success' : 'school_login_success';
                SecurityConfig::logSecurityEvent($eventType, [
                    'user_id' => $userData['id'],
                    'email' => $userData['email'],
                    'role' => $userRole,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);

                // Set remember me cookie if checked
                if ($rememberMe) {
                    setcookie('school_email', $email, time() + (30 * 24 * 60 * 60), '/');
                } else {
                    setcookie('school_email', '', time() - 3600, '/');
                }

                // Redirect based on role
                $success = 'Login successful! Redirecting...';
                if ($userRole === 'super_admin') {
                    header('Location: /School-SAAS/App/Modules/SAAS_admin/Views/dashboard/index.php');
                } else {
                    header('Location: /School-SAAS/App/Modules/School_Admin/Views/index.php');
                }
                exit;
            }

        } catch (PDOException $e) {
            $error = 'Database error: Unable to process login. Please try again later.';
            SecurityConfig::logSecurityEvent('school_login_error', [
                'email' => $email,
                'error' => $e->getMessage(),
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login - School SMS</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="School SMS - Student Management System" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../public/assets/css/style.css" />
    <style>
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-white">
    <!-- begin app -->
    <div class="app">
        <!-- begin app-wrap -->
        <div class="app-wrap">
            <!-- begin pre-loader -->
            <div class="loader" id="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>
            <!-- end pre-loader -->

            <!--start login contant-->
            <div class="app-contant">
                <div class="bg-white">
                    <div class="container-fluid p-0">
                        <div class="row no-gutters">
                            <div class="col-sm-6 col-lg-5 col-xxl-3  align-self-center order-2 order-sm-1">
                                <div class="d-flex align-items-center h-100-vh">
                                    <div class="login p-50">
                                        <h1 class="mb-2"><?php echo htmlspecialchars($loginContext); ?></h1>
                                        <p>Please enter your credentials to login.</p>

                                        <!-- Error Alert -->
                                        <?php if ($error): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Success Alert -->
                                        <?php if ($success): ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                                            </div>
                                        <?php endif; ?>

                                        <form method="POST" class="mt-3 mt-sm-5" novalidate>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Email*</label>
                                                        <input type="email" name="email" class="form-control" placeholder="Enter school email" 
                                                               value="<?php echo htmlspecialchars($rememberedEmail); ?>" required />
                                                        <small class="text-danger"></small>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Password*</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Password" required />
                                                        <small class="text-danger"></small>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="d-block d-sm-flex  align-items-center">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me" value="1">
                                                            <label class="form-check-label" for="rememberMe">
                                                                Remember Me
                                                            </label>
                                                        </div>
                                                        <a href="forgetpass.php" class="ml-auto">Forgot Password ?</a>
                                                    </div>
                                                </div>
                                                <div class="col-12 mt-3">
                                                    <button type="submit" class="btn btn-primary text-uppercase" id="loginBtn">Sign In</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xxl-9 col-lg-7 bg-gradient o-hidden order-1 order-sm-2">
                                <div class="row align-items-center h-100">
                                    <div class="col-7 mx-auto ">
                                        <img class="img-fluid" src="../../../public/assets/img/bg/login.svg" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end login contant-->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->



    <!-- plugins -->
    <script src="../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../public/assets/js/app.js"></script>

    <script>
        // Hide loader after page loads
        window.addEventListener('load', function() {
            document.getElementById('loader').style.display = 'none';
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="email"]');
            const password = document.querySelector('input[name="password"]');

            if (!email.value.trim()) {
                e.preventDefault();
                alert('Please enter email');
                email.focus();
                return false;
            }

            if (!password.value.trim()) {
                e.preventDefault();
                alert('Please enter password');
                password.focus();
                return false;
            }

            if (password.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters');
                password.focus();
                return false;
            }
        });
    </script>
</body>

</html>