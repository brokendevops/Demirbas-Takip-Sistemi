<?php
require 'db.php';
session_start();

$kullanici_id = $_POST['kullanici_id'] ?? null;

if (!$kullanici_id || !isset($_FILES['dosya'])) {
    die("Eksik veri.");
}

$dosya = $_FILES['dosya'];

if ($dosya['type'] !== 'application/pdf') {
    die("Sadece PDF dosyası yükleyebilirsiniz.");
}

$dosyaAdi = uniqid('ozluk_') . '.pdf';
$hedefYol = __DIR__ . "/uploads/ozluk/$dosyaAdi";

if (!is_dir(__DIR__ . "/uploads/ozluk")) {
    mkdir(__DIR__ . "/uploads/ozluk", 0777, true);
}

if (move_uploaded_file($dosya['tmp_name'], $hedefYol)) {
    $stmt = $conn->prepare("INSERT INTO ozluk_dosyalari (kullanici_id, dosya_adi, yuklenme_tarihi) VALUES (?, ?, NOW())");
    $stmt->execute([$kullanici_id, $dosyaAdi]);
    $_SESSION['success'] = "Dosya başarıyla yüklendi.";
} else {
    $_SESSION['success'] = "Dosya yüklenemedi.";
}

header("Location: kullanici_detay.php?id=$kullanici_id");
exit;
