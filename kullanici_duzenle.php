<?php
require 'db.php';
session_start();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Kullanıcı ID belirtilmedi.";
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Kullanıcı bilgilerini çek
$stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$id]);
$kullanici = $stmt->fetch();

if (!$kullanici) {
    $_SESSION['error'] = "Kullanıcı bulunamadı.";
    header("Location: index.php");
    exit;
}

// GÜNCELLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $ad_soyad = trim($_POST['ad_soyad']);
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE kullanicilar SET ad_soyad = ?, aktif = ? WHERE id = ?");
    $stmt->execute([$ad_soyad, $aktif, $id]);

    $_SESSION['success'] = "Kullanıcı bilgileri güncellendi.";
    header("Location: index.php");
    exit;
}

// SİLME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $conn->prepare("DELETE FROM demirbaslar WHERE kullanici_id = ?")->execute([$id]);
    $conn->prepare("DELETE FROM kullanicilar WHERE id = ?")->execute([$id]);

    $_SESSION['success'] = "Kullanıcı ve bağlı demirbaşlar silindi.";
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Düzenle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.20/dist/full.min.css" rel="stylesheet" type="text/css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap');
    </style>

</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white shadow-md rounded-lg p-6 w-full max-w-md">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
            <i data-feather="user"></i> Kullanıcı Düzenle
        </h2>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block mb-1 text-sm font-medium">Ad Soyad</label>
                <input type="text" name="ad_soyad" class="input input-bordered w-full bg-gray-50"
                    value="<?= htmlspecialchars($kullanici['ad_soyad']) ?>" required>
            </div>

            <div class="form-control">
                <label class="cursor-pointer label">
                    <span class="label-text">Aktif</span>
                    <input type="checkbox" name="aktif" class="checkbox checkbox-info text-gray-100"
                        <?= $kullanici['aktif'] ? 'checked' : '' ?>>
                </label>
            </div>

            <div class="flex justify-between items-center mt-6">
                <button type="submit" name="update" class="btn btn-success text-gray-100">
                    <i data-feather="save" class="w-4 h-4 mr-2"></i> Güncelle
                </button>
                <button type="submit" name="delete" class="btn btn-error text-gray-100"
                    onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">
                    <i data-feather="user-minus" class="w-4 h-4 mr-2"></i> Sil
                </button>
                <a href="index.php" class="btn btn-primary text-gray-100">
                    <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i> Geri Dön
                </a>
            </div>
        </form>
    </div>

    <script>feather.replace();</script>
</body>

</html>