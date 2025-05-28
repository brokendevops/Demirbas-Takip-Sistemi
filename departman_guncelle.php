<?php
require 'db.php';
session_start();

$kullanici_id = $_POST['kullanici_id'] ?? null;
$departman_id = $_POST['departman_id'] ?? null;

if ($kullanici_id && $departman_id) {
    $stmt = $conn->prepare("UPDATE kullanicilar SET departman_id = ? WHERE id = ?");
    $stmt->execute([$departman_id, $kullanici_id]);

    $_SESSION['success'] = "Departman başarıyla güncellendi.";
}

header("Location: kullanici_detay.php?id=" . $kullanici_id);
exit;
