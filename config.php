<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$host = 'localhost';
$dbname = 'school';
$user = 'root';
$password = 'root';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    ];
try{
    $pdo = new PDO($dsn, $user, $password, $options);
//    echo "数据库连接成功";
}catch (PDOException $e){
    die("数据库连接失败".$e->getMessage());
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}


function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}


function no_logged_in() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}