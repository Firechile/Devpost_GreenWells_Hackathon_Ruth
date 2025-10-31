<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// ==================== SIGNUP PROCESS ====================
if (isset($_POST['signup'])) {
    $firstname = $conn->real_escape_string(trim($_POST['firstname']));
    $companyname = $conn->real_escape_string(trim($_POST['companyname']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkEmail = $conn->query("SELECT email FROM sign_in_log WHERE email = '$email'");
    
    if ($checkEmail->num_rows > 0) {
        $_SESSION['signup_error'] = 'Email is already registered!';
        header("Location: signup.php");
        exit();
    }
    
    $insert = $conn->query("INSERT INTO sign_in_log (firstname, companyname, email, password) VALUES ('$firstname', '$companyname', '$email', '$password')");
    
    if ($insert) {
        $_SESSION['signup_success'] = 'Registration successful! Please login.';
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['signup_error'] = 'Registration failed. Please try again.';
        header("Location: signup.php");
        exit();
    }
}

// ==================== LOGIN PROCESS ====================
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    // Query database for user
    $result = $conn->query("SELECT * FROM sign_in_log WHERE email = '$email'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful - create session
            $_SESSION['user_id'] = $user['ID'];  // Changed from 'id' to 'ID'
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['companyname'] = $user['companyname'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Wrong password
            $_SESSION['login_error'] = 'Incorrect email or password';
            header("Location: login.php");
            exit();
        }
    } else {
        // Email not found
        $_SESSION['login_error'] = 'Incorrect email or password';
        header("Location: login.php");
        exit();
    }
}

$conn->close();
?>