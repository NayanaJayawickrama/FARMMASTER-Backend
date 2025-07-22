<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "farm_master#";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}
?>
