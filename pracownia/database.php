<?php

$host = 'localhost';
$dbname = 'pracownia';
$user = 'admin_mak';
$pass = 'BadxboxAdb7';

try {
    $pdo = new PDO('mysql:host='.$host.';dbname='.$dbname, $user, $pass);
    $pdo->query('SET NAMES utf8');
} catch (PDOException $e) {
    echo 'Połączenie nie mogło zostać utworzone: ' . $e->getMessage();
    exit();
}
?>
