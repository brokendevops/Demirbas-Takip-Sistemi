<?php
require 'db.php';
session_start();

$id = $_GET['id'] ?? null;
if (!$id)
    die("Demirbaş ID bulunamadı.");

// Dropdownlar
$turler = $conn->query("SELECT id, ad FROM turler")->fetchAll(PDO::FETCH_ASSOC);
$sirketler = $conn->query("SELECT id, ad FROM sirketler")->fetchAll(PDO::FETCH_ASSOC);
$lokasyonlar = $conn->query("SELECT id, ad FROM lokasyonlar")->fetchAll(PDO::FETCH_ASSOC);
$teslim_edenler = $conn->query("SELECT ad FROM teslim_edenler")->fetchAll(PDO::FETCH_ASSOC);

// Demirbaş bilgisi
$stmt = $conn->prepare("SELECT * FROM demirbaslar WHERE id = ?");
$stmt->execute([$id]);
$demirbas = $stmt->fetch();
if (!$demirbas)
    die("Demirbaş bulunamadı.");

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE demirbaslar SET tur_id=?, sirket_id=?, teslim_eden_id=?, lokasyon_id=?, model=?, gsm_no=?, imei_1_no=?, iccid_1_no=?, imei_2_no=?, seri_no=?, notlar=? WHERE id=?");
    $stmt->execute([
        $_POST['tur_id'],
        $_POST['sirket_id'],
        $_POST['teslim_eden_id'],
        $_POST['lokasyon_id'],
        trim($_POST['model']),
        trim($_POST['gsm_no']),
        trim($_POST['imei_1_no']),
        trim($_POST['iccid_1_no']),
        trim($_POST['imei_2_no']),
        trim($_POST['seri_no']),
        trim($_POST['notlar']),
        $id
    ]);
    $_SESSION['success'] = "Kullanıcı başarıyla güncellendi!";
    header("Location: kullanici_detay.php?id=" . $demirbas['kullanici_id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Demirbaş Güncelle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.css" rel="stylesheet" />
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap');
    </style>

</head>

<body class="bg-gray-50 text-sm">
    <div class="max-w-6xl mx-auto p-6">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2 text-gray-500">
            <i class="text-gray-500" data-feather="edit"></i>
            Demirbaş Güncelle
        </h2>

        <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tür -->
            <div>
                <label class="label">Tür</label>
                <select name="tur_id" class="select select-bordered w-full bg-gray-100">
                    <?php foreach ($turler as $tur): ?>
                        <option value="<?= $tur['id'] ?>" <?= $demirbas['tur_id'] == $tur['id'] ? 'selected' : '' ?>>
                            <?= $tur['ad'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Şirket -->
            <div>
                <label class="label">Şirket</label>
                <select name="sirket_id" class="select select-bordered w-full  bg-gray-100">
                    <?php foreach ($sirketler as $sirket): ?>
                        <option value="<?= $sirket['id'] ?>" <?= $demirbas['sirket_id'] == $sirket['id'] ? 'selected' : '' ?>>
                            <?= $sirket['ad'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Lokasyon -->
            <div>
                <label class="label">Lokasyon</label>
                <select name="lokasyon_id" class="select select-bordered w-full  bg-gray-100">
                    <?php foreach ($lokasyonlar as $lokasyon): ?>
                        <option value="<?= $lokasyon['id'] ?>" <?= $demirbas['lokasyon_id'] == $lokasyon['id'] ? 'selected' : '' ?>>
                            <?= $lokasyon['ad'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Teslim Eden -->
            <div>
                <label class="label">Teslim Eden</label>
                <select name="teslim_eden" class="select select-bordered w-full  bg-gray-100">
                    <?php foreach ($teslim_edenler as $teslim): ?>
                        <option value="<?= $teslim['ad'] ?>" <?= $demirbas['teslim_eden_id'] == $teslim['ad'] ? 'selected' : '' ?>>
                            <?= $teslim['ad'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Model -->
            <div>
                <label class="label">Model</label>
                <input type="text" name="model" class="input input-bordered w-full  bg-gray-100"
                    value="<?= $demirbas['model'] ?>">
            </div>

            <!-- GSM No -->
            <div>
                <label class="label">GSM No</label>
                <input type="text" name="gsm_no" class="input input-bordered w-full  bg-gray-100"
                    value="<?= $demirbas['gsm_no'] ?>">
            </div>

            <!-- IMEI 1 -->
            <div>
                <label class="label">IMEI 1</label>
                <input type="text" name="imei_1_no" class="input input-bordered w-full  bg-gray-100"
                    value="<?= $demirbas['imei_1_no'] ?>">
            </div>

            <!-- ICCID 1 -->
            <div>
                <label class="label">ICCID 1</label>
                <input type="text" name="iccid_1_no" class="input input-bordered w-full  bg-gray-100"
                    value="<?= $demirbas['iccid_1_no'] ?>">
            </div>

            <!-- IMEI 2 -->
            <div>
                <label class="label">IMEI 2</label>
                <input type="text" name="imei_2_no" class="input input-bordered w-full bg-gray-100"
                    value="<?= $demirbas['imei_2_no'] ?>">
            </div>

            <!-- Seri No -->
            <div>
                <label class="label">Seri No</label>
                <input type="text" name="seri_no" class="input input-bordered w-full  bg-gray-100"
                    value="<?= $demirbas['seri_no'] ?>">
            </div>

            <!-- Notlar -->
            <div class="md:col-span-3">
                <label class="label">Notlar</label>
                <textarea name="notlar"
                    class="textarea textarea-bordered w-full  bg-gray-100"><?= $demirbas['notlar'] ?></textarea>
            </div>

            <!-- Butonlar -->
            <div class="md:col-span-3 flex justify-end gap-4 mt-4">
                <a href="kullanici_detay.php?id=<?= $demirbas['kullanici_id'] ?>" class="btn btn-neutral">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 stroke-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                    Geri
                </a>
                <button type="submit" class="btn btn-primary text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 stroke-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M5 13l4 4L19 7" />
                    </svg>
                    Güncelle
                </button>
            </div>
        </form>
    </div>


    <script>
        feather.replace();
    </script>



</body>

</html>