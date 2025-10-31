<?php
$servername = "sql206.infinityfree.com"; 
$username = "if0_40251708";
$password = "uDszqH8P19M";
$database = "if0_40251708_biogas_app"; 

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
