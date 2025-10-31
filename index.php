<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp</title>
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="validation.js" defer></script>
</head>
<body>
  <?php
session_start();
if (isset($_SESSION['signup_error'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('error__message').textContent = '" . $_SESSION['signup_error'] . "';
        });
    </script>";
    unset($_SESSION['signup_error']);
}
?>
  <div class="wrapper">
   <h1>GasConnect</h1>
   <h2>Register</h2>
   <p id="error__message"></p>
   <form id="form" action="login_register.php" method="post">
    <div>
      <label for="firstname__input">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Z"/></svg>
      </label>
      <input type="text" name="firstname" id="firstname__input" placeholder="Firstname">
    </div>
    <div>
      <label for="companyname__input">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M160-120q-33 0-56.5-23.5T80-200v-440q0-33 23.5-56.5T160-720h160v-80q0-33 23.5-56.5T400-880h160q33 0 56.5 23.5T640-800v80h160q33 0 56.5 23.5T880-640v440q0 33-23.5 56.5T800-120H160Zm240-600h160v-80H400v80Z"/></svg>
      </label>
      <input type="text" name="companyname" id="companyname__input" placeholder="Company name">
    </div>
    <div>
      <label for="email__input">
        <span>@</span>
      </label>
      <input type="email" name="email" id="email__input" placeholder="Email Address">
    </div>
    <div>
      <label for="password__input">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm240-200q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"/></svg>
      </label>
      <input type="password" name="password" id="password__input" placeholder="Password">
    </div>
    <div>
      <label for="repeat__password__input">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm240-200q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"/></svg>
      </label>
      <input type="password" name="repeat-password" id="repeat__password__input" placeholder="Repeat password">
    </div>
    <button type="submit" name="signup">SignUp</button>
   </form>
   <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>
</html>