<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ameliorec"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8");
?>
