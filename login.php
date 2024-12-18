<?php
  // Set session cookie parameters to make it accessible across all directories
  session_set_cookie_params(0, '/');
  session_start();

  // Check if the user is logged in and has admin rights
  if (isset($_SESSION["userid"])) {
    // Redirect the user to a different page or display an error message
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
    <title>Champs Chance</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=052523">
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="icon" type="image/png" href="img/favicon-16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="img/favicon-32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="img/favicon-48.png" sizes="48x48">
    <link rel="icon" type="image/png" href="img/favicon-64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="img/favicon-128.png" sizes="128x128">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" charset="utf-8"></script>
</head>

<body>
    
    <?php
        include_once 'header.php';
    ?>
    <script src="js/breadcrumbs.js"></script>

    <div class="main-content-signin">

        <div class="signin-container-parent">
            <div class="login-buttons">
                <h4><div class="signin-btn"><label>Sign In</label></div></h4>
                <h4><div class="signup-btn"><label>Register</label></div></h4>
            </div>

            <div class="signin-stuff">
                <div class="signin-form">
                    
                    <?php
                        if (isset($_GET["login-error"])) {
                            if ($_GET["login-error"] == "empty-login") {
                                echo "<div class=\"error-message\">Every field is required.</div>";
                            }
                            if ($_GET["login-error"] == "wrong-login") {
                                echo "<div class=\"error-message\">Wrong username / email.</div>";
                            }
                            if ($_GET["login-error"] == "wrong-password") {
                                echo "<div class=\"error-message\">Wrong password.</div>";
                            }
                            if ($_GET["login-error"] == "invalid-username") {
                                echo "<div class=\"error-message\">Invalid username.</div>";
                            }
                        }
                        else if (isset($_GET["register-success"])) {
                            if ($_GET["register-success"] == "createdaccount") {
                                echo "<div class=\"success-message\">You've successfully signed up! You can now sign in.</div>";
                            }
                        }
                    ?>

                    <form action="includes/login.inc.php" method="post">
                        <input type="text" name="uid" placeholder="username or email">
                        <input type="password" name="pwd" placeholder="password">
                        <a class="forgot-btn" href="#">Forgot password?</a>
                        <button type="submit" name="submit">Log In</button>
                    </form>
                </div>

                <div class="forgot-form">
                    <?php
                        if (isset($_GET["forgot-error"])) {
                            if ($_GET["login-error"] == "empty-login") {
                                echo "<div class=\"error-message\">Every field is required.</div>";
                            }
                            if ($_GET["forgot-error"] == "no-user-found") {
                                echo "<div class=\"error-message\">No user found with that email / username.</div>";
                            }
                        }
                    ?>
                    <p>If you've forgotten your password, enter your username or email below.</p>
                    <p>A link to reset your password will be sent to the email address tied to the account.</p>
                    <form action="includes/forgot.inc.php" method="post">
                        <input type="text" name="uid" placeholder="username or email">
                        <button type="submit" name="submit">Reset Password</button>
                    </form>
                </div>

                <div class="signup-form">
                    <?php
                        if (isset($_GET["register-error"])) {
                            if ($_GET["register-error"] == "empty-input") {
                                echo "<div class=\"error-message\">Every field is required.</div>";
                                echo '<script>register();</script>';
                            }
                            else if ($_GET["register-error"] == "invalid-username") {
                                echo "<div class=\"error-message\">Invalid username.</div>";
                                echo '<script>register();</script>';
                            }
                            else if ($_GET["register-error"] == "invalid-email") {
                                echo "<div class=\"error-message\">Invalid Email.</div>";
                                echo '<script>register();</script>';
                            }
                            else if ($_GET["register-error"] == "password-mismatch") {
                                echo "<div class=\"error-message\">Passwords don't match, retype them both.</div>";
                                echo '<script>register();</script>';
                            }
                            else if ($_GET["register-error"] == "username-email-taken") {
                                echo "<div class=\"error-message\">Either the username or the email are already in use.</div>";
                                echo '<script>register();</script>';
                            }
                            else if ($_GET["register-error"] == "stmt-failed") {
                                echo "<div class=\"error-message\">Something went wrong, please try again.</div>";
                                echo '<script>register();</script>';
                            }
                            else if ($_GET["register-error"] == "none") {
                                echo "<div class=\"success-message\">You've successfully signed up! You can now sign in.</div>";
                                echo '<script>register();</script>';
                            }
                        }
                        else if (isset($_GET["success"])) {
                            if ($_GET["success"] == "createdaccount") {
                                echo "<div class=\"success-message\">You've successfully signed up! You can now sign in.</div>";
                            }
                        }
                    ?>
                    <form action="includes/signup.inc.php" method="post" id="signupForm">
                        <input type="text" name="firstname" placeholder="First name" required>
                        <input type="text" name="lastname" placeholder="Last name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="uid" placeholder="Username" required minlength="3" maxlength="50">
                        <input type="password" name="pwd" placeholder="Password" required minlength="8">
                        <input type="password" name="pwdrepeat" placeholder="Repeat password" required>
                        <button type="submit" name="submit">Sign Up</button>
                    </form>
                </div>
            </div>
            
            
            

        </div>
    </div>
    
    <?php
        include_once 'footer.php';
    ?>

        
    </div>
</body>
</html>

<script src="js/login_form.js"></script>