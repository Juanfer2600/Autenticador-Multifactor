<?php

//conexion con la base de datos autenticador-multifactor
$servername = "localhost";
$pass = "";
$username = "root";
$dbname = "autenticador_multifactor";

$conn = mysqli_connect($servername, $username, $pass, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Set the character set to utf8mb4
$conn->set_charset("utf8mb4");
