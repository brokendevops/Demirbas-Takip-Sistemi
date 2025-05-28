<?php
require 'db.php'; // PDO bağlantısı
session_start();

// Dropdown verileri
$turler = $conn->query("SELECT id, ad FROM turler")->fetchAll(PDO::FETCH_ASSOC);
$sirketler = $conn->query("SELECT id, ad FROM sirketler")->fetchAll(PDO::FETCH_ASSOC);
$lokasyonlar = $conn->query("SELECT id, ad FROM lokasyonlar")->fetchAll(PDO::FETCH_ASSOC);
$teslim_edenler = $conn->query("SELECT id, ad FROM teslim_edenler")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_soyad = trim($_POST['ad_soyad']);
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO kullanicilar (ad_soyad, aktif) VALUES (?, ?)");
    $stmt->execute([$ad_soyad, $aktif]);
    $kullanici_id = $conn->lastInsertId();

    if (isset($_POST['demirbas']) && is_array($_POST['demirbas'])) {
        foreach ($_POST['demirbas'] as $demirbas) {
            $stmt2 = $conn->prepare("INSERT INTO demirbaslar 
                (kullanici_id, tur_id, sirket_id, teslim_eden_id, lokasyon_id, model, gsm_no, imei_1_no, iccid_1_no, imei_2_no, seri_no, notlar) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->execute([
                $kullanici_id,
                $demirbas['tur_id'],
                $demirbas['sirket_id'],
                $_POST['teslim_eden_id'], // Burayı POST'tan al
                $demirbas['lokasyon_id'],
                trim($demirbas['model']),
                trim($demirbas['gsm_no']),
                trim($demirbas['imei_1_no']),
                trim($demirbas['iccid_1_no']),
                trim($demirbas['imei_2_no']),
                trim($demirbas['seri_no']),
                trim($demirbas['notlar'])
            ]);
        }
    }

    // Özlük Dosyası Yükleme
    if (isset($_FILES['ozluk_dosyasi']) && $_FILES['ozluk_dosyasi']['error'] === UPLOAD_ERR_OK) {
        $dosya_tmp = $_FILES['ozluk_dosyasi']['tmp_name'];
        $orijinal_ad = basename($_FILES['ozluk_dosyasi']['name']);
        $hedef_klasor = 'uploads/ozluk/';
        if (!is_dir($hedef_klasor))
            mkdir($hedef_klasor, 0777, true);

        $yeni_ad = uniqid() . "_" . $orijinal_ad;
        $hedef_yol = $hedef_klasor . $yeni_ad;

        if (move_uploaded_file($dosya_tmp, $hedef_yol)) {
            $stmt3 = $conn->prepare("INSERT INTO ozluk_dosyalar (kullanici_id, dosya_adi) VALUES (?, ?)");
            $stmt3->execute([$kullanici_id, $yeni_ad]);
        }
    }

    $_SESSION['success'] = "Kullanıcı, demirbaş ve özlük dosyası başarıyla eklendi!";
    header("Location: kullanici_detay.php?id=" . $kullanici_id);
    exit;
}
?>

<!-- HTML BAŞLANGIÇ -->
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcı ve Demirbaş Ekle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@3.8.1/dist/full.css" rel="stylesheet" />
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap');
    </style>

</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-10">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">👤 Kullanıcı ve 📦 Demirbaş Ekle</h1>
        </div>

        <form method="post" enctype="multipart/form-data" class="space-y-6">
            <!-- Kullanıcı Bilgileri -->
            <div class="card bg-white shadow-md">
                <div class="card-body">
                    <h2 class="card-title">👤 Kullanıcı Bilgileri</h2>
                    <div class="form-control">
                        <label class="label">Ad Soyad</label>
                        <input type="text" name="ad_soyad" class="input input-info input-bordered bg-white" required>
                    </div>
                    <label class="label cursor-pointer mt-3">
                        <span class="label-text">Aktif mi?</span>
                        <input type="checkbox" name="aktif" class="toggle toggle-success" checked />
                    </label>
                </div>
            </div>

            <!-- Demirbaş Alanı -->
            <div id="demirbas-container" class="space-y-6">
                <div class="card bg-white shadow-md demirbas-block">
                    <div class="card-body space-y-4">
                        <div class="flex justify-between items-center">
                            <h2 class="card-title">📦 Demirbaş Bilgisi</h2>
                            <button type="button" class="btn btn-sm btn-error remove-demirbas text-gray-100"><i
                                    data-feather="trash-2"></i> Sil</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="label">Tür</label>
                                <select name="demirbas[0][tur_id]" class="select select-bordered bg-white">
                                    <?php foreach ($turler as $tur): ?>
                                        <option value="<?= $tur['id'] ?>"><?= htmlspecialchars($tur['ad']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="label">Şirket</label>
                                <select name="demirbas[0][sirket_id]" class="select select-bordered bg-white">
                                    <?php foreach ($sirketler as $sirket): ?>
                                        <option value="<?= $sirket['id'] ?>"><?= htmlspecialchars($sirket['ad']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="label">Lokasyon</label>
                                <select name="demirbas[0][lokasyon_id]" class="select select-bordered bg-white">
                                    <?php foreach ($lokasyonlar as $lokasyon): ?>
                                        <option value="<?= $lokasyon['id'] ?>"><?= htmlspecialchars($lokasyon['ad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="label">Teslim Eden</label>
                                <select name="teslim_eden_id" class="select select-bordered bg-white">
                                    <?php foreach ($teslim_edenler as $teslim): ?>
                                        <option value="<?= $teslim['id'] ?>"><?= htmlspecialchars($teslim['ad']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div><label class="label ">Model</label><input name="demirbas[0][model]"
                                    class="input input-bordered bg-white"></div>
                            <div><label class="label">GSM No</label><input name="demirbas[0][gsm_no]"
                                    class="input input-bordered bg-white"></div>
                            <div><label class="label">IMEI 1</label><input name="demirbas[0][imei_1_no]"
                                    class="input input-bordered bg-white"></div>
                            <div><label class="label">ICCID 1</label><input name="demirbas[0][iccid_1_no]"
                                    class="input input-bordered bg-white"></div>
                            <div><label class="label">IMEI 2</label><input name="demirbas[0][imei_2_no]"
                                    class="input input-bordered bg-white"></div>
                            <div><label class="label">Seri No</label><input name="demirbas[0][seri_no]"
                                    class="input input-bordered bg-white"></div>
                        </div>
                        <div>
                            <label class="label">Notlar</label>
                            <textarea name="demirbas[0][notlar]"
                                class="textarea textarea-md textarea-bordered bg-white w-full" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Özlük Dosyası -->
            <div class="card bg-white shadow-md">
                <div class="card-body">
                    <h2 class="card-title">📁 Özlük Dosyası</h2>
                    <div class="form-control">
                        <label class="label">PDF Yükle</label>
                        <input type="file" name="ozluk_dosyasi" accept="application/pdf"
                            class="file-input file-input-bordered file-input-info w-full bg-white" />
                    </div>
                </div>
            </div>

            <!-- Butonlar -->
            <div class="flex justify-between items-center">
                <button type="button" class="btn btn-primary" id="add-demirbas">
                    <i data-feather="plus-circle" class="mr-1"></i> Yeni Demirbaş Ekle
                </button>
                <button type="submit" class="btn btn-success text-white">
                    <i data-feather="save" class="mr-1 text-white"></i> Kaydet ve Devam Et
                </button>
            </div>
            <a href="index.php" class="btn btn-outline mt-4"><i data-feather="arrow-left"></i> Geri Dön</a>
        </form>
    </div>

    <!-- JS -->
    <script>
        feather.replace();
        let demirbasIndex = 1;
        document.getElementById('add-demirbas').addEventListener('click', function () {
            const container = document.getElementById('demirbas-container');
            const block = container.querySelector('.demirbas-block').cloneNode(true);
            block.querySelectorAll('select, input, textarea').forEach(el => {
                const name = el.getAttribute('name');
                const newName = name.replace(/\[\d+\]/, `[${demirbasIndex}]`);
                el.setAttribute('name', newName);
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') el.value = '';
            });
            container.appendChild(block);
            feather.replace(); // iconları tekrar yükle
            demirbasIndex++;
        });

        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-demirbas')) {
                const blocks = document.querySelectorAll('.demirbas-block');
                if (blocks.length > 1) e.target.closest('.demirbas-block').remove();
            }
        });
    </script>
</body>

</html>