<?php
$host = 'localhost';$dbname = 'sistema_avaliacao';
$username = 'root';$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cria a base de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$dbname`;");

} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>