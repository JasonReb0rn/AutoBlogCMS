<?php

function emptyInputSignup($firstname, $lastname, $email, $username, $pwd, $pwdRepeat) {
    return empty($firstname) || empty($lastname) || empty($email) || 
           empty($username) || empty($pwd) || empty($pwdRepeat);
}

function invalidUsername($username) {
    return !preg_match("/^[a-zA-Z0-9_]{3,50}$/", $username);
}

function invalidEmail($email) {
    return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

function pwdMatch($pwd, $pwdRepeat) {
    return $pwd === $pwdRepeat;
}

function usernameOrEmailExists($pdo, $username, $email) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE Username = ? OR Email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetch() ? true : false;
    } catch (PDOException $e) {
        header("location: ../login.php?register-error=stmt-failed");
        exit();
    }
}

function createUser($pdo, $firstname, $lastname, $email, $username, $pwd) {
    try {
        $pdo->beginTransaction();
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO Users (Username, PasswordHash, Email) VALUES (?, ?, ?)");
        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
        $stmt->execute([$username, $hashedPwd, $email]);
        
        // Get the user ID
        $userId = $pdo->lastInsertId();
        
        // Store first/last name in UserPreferences
        $stmt = $pdo->prepare("INSERT INTO UserPreferences (UserID, KeyName, Value) VALUES (?, 'firstname', ?), (?, 'lastname', ?)");
        $stmt->execute([$userId, $firstname, $userId, $lastname]);
        
        $pdo->commit();
        header("location: ../login.php?register-success=createdaccount");
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("location: ../login.php?register-error=stmt-failed");
        exit();
    }
}

function emptyInputLogin($username, $pwd) {
    return empty($username) || empty($pwd);
}

function loginUser($pdo, $username, $pwd) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE Username = ? OR Email = ?");
        $stmt->execute([$username, $username]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pwdHashed = $row["PasswordHash"];
            if (password_verify($pwd, $pwdHashed)) {
                session_start();
                $_SESSION["userid"] = $row["UserID"];
                $_SESSION["username"] = $row["Username"];
                $_SESSION["role"] = $row["Role"];
                
                header("location: ../home.php");
                exit();
            } else {
                header("location: ../login.php?login-error=wrong-password");
                exit();
            }
        } else {
            header("location: ../login.php?login-error=wrong-login");
            exit();
        }
        
    } catch (PDOException $e) {
        header("location: ../login.php?login-error=stmt-failed");
        exit();
    }
}