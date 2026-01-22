<?php
session_start();

// Include auth controller and database
require_once __DIR__ . '/controller/authcontroller.php';
require_once __DIR__ . '/../../Config/database.php';
require_once __DIR__ . '/../../Core/database.php';

// Get database connection
$db = Database::connect();
$authController = new AuthController($db);

// Redirect if already logged in
if ($authController->isLoggedIn()) {
    header('Location: /School-SAAS/App/Modules/SAAS_admin/Views/dashboard/index.php');
    exit;
}

// Check if there's a remembered email
$rememberedEmail = $_COOKIE['email'] ?? '';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = $_POST['remember_me'] ?? false;

    $result = $authController->login($email, $password);

    if ($result['success']) {
        // Set remember me cookie if checked
        if ($rememberMe) {
            setcookie('email', $email, time() + (30 * 24 * 60 * 60), '/');
        }
        $success = 'Login successful! Redirecting...';
        header('Location: /School-SAAS/App/Modules/SAAS_admin/Views/dashboard/index.php');
        exit;
    } else {
        $error = $result['message'];
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
                                        <h1 class="mb-2">Welcome to SMS</h1>
                                        <p>Welcome back, please login to your account.</p>

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
                                                        <input type="email" name="email" class="form-control" placeholder="Enter your email" 
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
                                                <div class="col-12  mt-3">
                                                    <p>Don't have an account ?<a href="Signup.php"> Sign Up</a></p>
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