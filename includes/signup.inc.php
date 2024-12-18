<?php
// includes/signup.inc.php
require_once 'dbh.inc.php';
require_once 'login-functions.inc.php';

if (isset($_POST["submit"])) {
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $email = $_POST["email"];
    $username = $_POST["uid"];
    $pwd = $_POST["pwd"];
    $pwdRepeat = $_POST["pwdrepeat"];
    
    // Error handling
    if (emptyInputSignup($firstname, $lastname, $email, $username, $pwd, $pwdRepeat)) {
        header("location: ../login.php?register-error=empty-input");
        exit();
    }
    if (invalidUsername($username)) {
        header("location: ../login.php?register-error=invalid-username");
        exit();
    }
    if (invalidEmail($email)) {
        header("location: ../login.php?register-error=invalid-email");
        exit();
    }
    if (pwdMatch($pwd, $pwdRepeat) !== true) {
        header("location: ../login.php?register-error=password-mismatch");
        exit();
    }
    if (usernameOrEmailExists($pdo, $username, $email)) {
        header("location: ../login.php?register-error=username-email-taken");
        exit();
    }
    
    createUser($pdo, $firstname, $lastname, $email, $username, $pwd);
} else {
    header("location: ../login.php");
    exit();
}