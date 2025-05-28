<?php
require 'db.php';
session_start();

$id = $_GET['id'] ?? null;
$uid = $_GET['uid'] ?? null;

if (!$id || !$uid) {
    die("Eksik veri.");
}

// Dosya adı al
$stmt = $conn->prepare("SELECT dosya_adi FROM ozluk_dosyalari WHERE id = ?");
$stmt->execute([$id]);
$dosya = $stmt->fetch();

if ($dosya) {
    $yol = __DIR__ . '/uploads/ozluk/' . $dosya['dosya_adi'];
    if (file_exists($yol)) {
        unlink($yol);
    }

    // DB'den sil
    $conn->prepare("DELETE FROM ozluk_dosyalari WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Dosya silindi.";
}

header("Location: detay.php?id=$uid");
exit;
