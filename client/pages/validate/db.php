<?php

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'smartq_db';

$conn = new mysqli($servername, $username, $password, $dbname);

try{
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}
