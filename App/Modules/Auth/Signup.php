<?php
session_start();

// Include auth controller
require_once __DIR__ . '/controller/authcontroller.php';

$authController = new AuthController();

// Redirect if already logged in
if ($authController->isLoggedIn()) {
    header('Location: /School-SAAS/index.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';

    // Validate input
    if (!$username || !$email || !$password || !$confirmPassword) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Register user
        $result = $authController->register([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'student'
        ]);

        if ($result['success']) {
            $success = 'Registration successful! Redirecting to login...';
            header('Refresh: 2; URL=/School-SAAS/App/Modules/Auth/login.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Register - School SMS</title>
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

            <!--start register content-->
            <div class="app-contant">
                <div class="bg-white">
                    <div class="container-fluid p-0">
                        <div class="row no-gutters">
                            <div class="col-sm-6 col-lg-5 col-xxl-3  align-self-center order-2 order-sm-1">
                                <div class="d-flex align-items-center h-100-vh">
                                    <div class="register p-5">
                                        <h1 class="mb-2">Register to SMS</h1>
                                        <p>Welcome, Please create your account.</p>

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

                                        <form method="POST" class="mt-2 mt-sm-5" novalidate>
                                            <div class="row">
                                                <div class="col-12 col-sm-6">
                                                    <div class="form-group">
                                                        <label class="control-label">First Name*</label>
                                                        <input type="text" name="first_name" class="form-control" placeholder="First Name" 
                                                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Last Name*</label>
                                                        <input type="text" name="last_name" class="form-control" placeholder="Last Name" 
                                                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Email*</label>
                                                        <input type="email" name="email" class="form-control" placeholder="Email" required
                                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Username*</label>
                                                        <input type="text" name="username" class="form-control" placeholder="Username" required
                                                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Password*</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Password" required />
                                                        <small class="text-muted">Minimum 6 characters</small>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Confirm Password*</label>
                                                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required />
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="agreeTerms" name="agree_terms" required>
                                                        <label class="form-check-label" for="agreeTerms">
                                                            I accept terms & policy
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mt-3">
                                                    <button type="submit" class="btn btn-primary text-uppercase">Sign up</button>
                                                </div>
                                                <div class="col-12  mt-3">
                                                    <p>Already have an account ? <a href="login.php">Sign In</a></p>
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
            <!--end register content-->
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
            const username = document.querySelector('input[name="username"]');
            const email = document.querySelector('input[name="email"]');
            const password = document.querySelector('input[name="password"]');
            const confirmPassword = document.querySelector('input[name="confirm_password"]');
            const agreeTerms = document.querySelector('input[name="agree_terms"]');

            if (!username.value.trim()) {
                e.preventDefault();
                alert('Please enter username');
                username.focus();
                return false;
            }

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

            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match');
                confirmPassword.focus();
                return false;
            }

            if (!agreeTerms.checked) {
                e.preventDefault();
                alert('Please accept terms & policy');
                agreeTerms.focus();
                return false;
            }
        });
    </script>
</body>

</html>