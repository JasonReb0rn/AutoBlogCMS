<?php

require_once 'dbh.inc.php';
require_once 'login-functions.inc.php';

if (isset($_POST["submit"])) {
    $username = $_POST["uid"];
    $pwd = $_POST["pwd"];
    
    if (emptyInputLogin($username, $pwd)) {
        header("location: ../login.php?login-error=empty-login");
        exit();
    }
    
    loginUser($pdo, $username, $pwd);
} else {
    header("location: ../login.php");
    exit();
}