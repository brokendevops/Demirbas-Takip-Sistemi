<?php
require 'db.php';
session_start();

$id = $_GET['id'] ?? null;
$uid = $_GET['uid'] ?? null;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM demirbaslar WHERE id = ?");
    $stmt->execute([$id]);
}


$_SESSION['success'] = "Kullanıcı ve ilişkili demirbaşlar başarıyla silindi!";
header("Location: kullanici_detay.php?id=" . $uid);
exit;
