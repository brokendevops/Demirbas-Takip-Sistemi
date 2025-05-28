<?php
require 'db.php';
session_start();

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Kullanıcı ID bulunamadı.");
}

// Kullanıcı bilgisi (departman bilgisi dahil)
$stmt = $conn->prepare("SELECT k.*, d.ad as departman_ad
    FROM kullanicilar k
    LEFT JOIN departmanlar d ON k.departman_id = d.id
    WHERE k.id = ?");
$stmt->execute([$id]);
$kullanici = $stmt->fetch();
if (!$kullanici) {
    die("Kullanıcı bulunamadı.");
}

// Demirbaş listesi
$stmt2 = $conn->prepare("SELECT d.*, t.ad as tur_ad, s.ad as sirket_ad, l.ad as lokasyon_ad, te.ad as teslim_eden_ad
    FROM demirbaslar d
    LEFT JOIN turler t ON d.tur_id = t.id
    LEFT JOIN sirketler s ON d.sirket_id = s.id
    LEFT JOIN lokasyonlar l ON d.lokasyon_id = l.id
    LEFT JOIN teslim_edenler te ON d.teslim_eden_id = te.id
    WHERE d.kullanici_id = ?");

$stmt2->execute([$id]);
$demirbaslar = $stmt2->fetchAll(PDO::FETCH_ASSOC);


// Diğer veriler
$turler = $conn->query("SELECT * FROM turler")->fetchAll();
$sirketler = $conn->query("SELECT * FROM sirketler")->fetchAll();
$lokasyonlar = $conn->query("SELECT * FROM lokasyonlar")->fetchAll();
$teslim_edenler = $conn->query("SELECT * FROM teslim_edenler")->fetchAll();
$departmanlar = $conn->query("SELECT * FROM departmanlar")->fetchAll();

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Detay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap');
    </style>

</head>

<body class="bg-base-100 text-base-content">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold">
                <?= htmlspecialchars($kullanici['ad_soyad']) ?>
                <span class="badge text-gray-100 <?= $kullanici['aktif'] ? 'badge-success' : 'badge-error' ?>">
                    <?= $kullanici['aktif'] ? 'Aktif' : 'Pasif' ?>
                </span>
            </h2>
            <p class="text-sm text-gray-500">
                Departman:
                <?php if ($kullanici['departman_ad']): ?>
                    <strong><?= htmlspecialchars($kullanici['departman_ad']) ?></strong>
                <?php else: ?>
                    <span class="text-error">Atanmamış</span>
                <?php endif; ?>
            </p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success shadow-lg mb-4 text-gray-100">
                <i data-feather="user-check"></i>
                <div>
                    <span><?= $_SESSION['success'] ?></span>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Departman Güncelleme Formu -->
        <div class="card bg-base-100 shadow-md my-6">
            <div class="card-body">
                <h2 class="card-title">Departman Güncelle</h2>
                <form action="departman_guncelle.php" method="post" class="flex gap-4 items-end">
                    <input type="hidden" name="kullanici_id" value="<?= $kullanici['id'] ?>">
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text">Departman Seç</span></label>
                        <select name="departman_id" class="select select-bordered w-full" required>
                            <option value="">Seçiniz</option>
                            <?php foreach ($departmanlar as $dep): ?>
                                <option value="<?= $dep['id'] ?>" <?= $kullanici['departman_id'] == $dep['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dep['ad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary text-gray-200"><i
                            data-feather="edit"></i>Güncelle</button>
                </form>
            </div>
        </div>

        <!-- Demirbaş Tablosu -->
        <div class="overflow-x-auto mb-8">
            <h3 class="text-lg font-semibold mb-2">Demirbaşlar</h3>
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Tür</th>
                        <th>Şirket</th>
                        <th>Lokasyon</th>
                        <th>Teslim Eden</th>
                        <th>Model</th>
                        <th>GSM</th>
                        <th>Seri No</th>
                        <th>Notlar</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demirbaslar as $demirbas): ?>
                        <tr>
                            <td><?= htmlspecialchars($demirbas['tur_ad']) ?></td>
                            <td><?= htmlspecialchars($demirbas['sirket_ad']) ?></td>
                            <td><?= htmlspecialchars($demirbas['lokasyon_ad']) ?></td>
                            <td><?= htmlspecialchars($demirbas['teslim_eden_ad'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($demirbas['model']) ?></td>
                            <td><?= htmlspecialchars($demirbas['gsm_no']) ?></td>
                            <td><?= htmlspecialchars($demirbas['seri_no']) ?></td>
                            <td><?= htmlspecialchars($demirbas['notlar']) ?></td>
                            <td>
                                <a href="duzenle.php?id=<?= $demirbas['id'] ?>"
                                    class="btn btn-sm btn-warning text-gray-200">Düzenle</a>
                                <a href="demirbas_sil.php?id=<?= $demirbas['id'] ?>&uid=<?= $id ?>"
                                    class="btn btn-sm btn-error text-gray-100"
                                    onclick="return confirm('Silmek istediğinize emin misiniz?')">
                                    Sil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Demirbaş Ekleme Formu -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title">Yeni Demirbaş Ekle</h2>
                <form action="demirbas_ekle.php" method="post">
                    <input type="hidden" name="kullanici_id" value="<?= $kullanici['id'] ?>">
                    <div id="demirbasListesi">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 demirbas-item border p-4 rounded relative mb-4">
                            <button type="button"
                                class="btn btn-sm btn-circle btn-error absolute top-0 right-0 m-1 remove-btn">✕</button>

                            <select name="tur_id[]" class="select select-bordered w-full" required>
                                <option value="">Tür Seçiniz</option>
                                <?php foreach ($turler as $tur): ?>
                                    <option value="<?= $tur['id'] ?>"><?= htmlspecialchars($tur['ad']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="sirket_id[]" class="select select-bordered w-full" required>
                                <option value="">Şirket Seçiniz</option>
                                <?php foreach ($sirketler as $sirket): ?>
                                    <option value="<?= $sirket['id'] ?>"><?= htmlspecialchars($sirket['ad']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="lokasyon_id[]" class="select select-bordered w-full" required>
                                <option value="">Lokasyon Seçiniz</option>
                                <?php foreach ($lokasyonlar as $lokasyon): ?>
                                    <option value="<?= $lokasyon['id'] ?>"><?= htmlspecialchars($lokasyon['ad']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="teslim_eden_id[]" class="select select-bordered w-full" required>
                                <option value="">Teslim Eden</option>
                                <?php foreach ($teslim_edenler as $personel): ?>
                                    <option value="<?= $personel['id'] ?>"><?= htmlspecialchars($personel['ad']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="model[]" class="input input-bordered w-full" placeholder="Model">
                            <input type="text" name="gsm_no[]" class="input input-bordered w-full" placeholder="GSM No">
                            <input type="text" name="imei_1_no[]" class="input input-bordered w-full"
                                placeholder="IMEI 1 No">
                            <input type="text" name="iccid_1_no[]" class="input input-bordered w-full"
                                placeholder="ICCID 1 No">
                            <input type="text" name="imei_2_no[]" class="input input-bordered w-full"
                                placeholder="IMEI 2 No">
                            <input type="text" name="seri_no[]" class="input input-bordered w-full"
                                placeholder="Seri No">
                            <input type="text" name="notlar[]" class="input input-bordered w-full" placeholder="Notlar">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-between">
                        <button type="button" class="btn btn-outline" id="yeniSatirEkle"> <i
                                data-feather="plus"></i>Yeni Demirbaş Ekle</button>
                        <button type="submit" class="btn btn-success text-gray-100"><i
                                data-feather="save"></i>Kaydet</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Özlük Dosyaları -->
        <div class="card bg-base-100 shadow-lg my-6">
            <div class="card-body">
                <h2 class="card-title">Özlük Dosyaları</h2>

                <!-- Mevcut Dosyaları Listele -->
                <?php
                $stmt3 = $conn->prepare("SELECT * FROM ozluk_dosyalari WHERE kullanici_id = ?");
                $stmt3->execute([$kullanici['id']]);
                $dosyalar = $stmt3->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (count($dosyalar) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>Dosya Adı</th>
                                    <th>Tarih</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dosyalar as $dosya): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($dosya['dosya_adi']) ?></td>
                                        <td><?= htmlspecialchars($dosya['yuklenme_tarihi']) ?></td>
                                        <td>
                                            <a href="uploads/ozluk/<?= $dosya['dosya_adi'] ?>" target="_blank"
                                                class="btn btn-sm btn-info text-white">
                                                <i data-feather="eye"></i> Görüntüle
                                            </a>
                                            <a href="ozluk_sil.php?id=<?= $dosya['id'] ?>&uid=<?= $kullanici['id'] ?>"
                                                onclick="return confirm('Dosyayı silmek istediğinizden emin misiniz?')"
                                                class="btn btn-sm btn-error text-white">
                                                <i data-feather="trash-2"></i> Sil
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Henüz özlük dosyası eklenmemiş.</p>
                <?php endif; ?>

                <!-- Dosya Yükleme Formu -->
                <form action="ozluk_yukle.php" method="post" enctype="multipart/form-data" class="mt-6">
                    <input type="hidden" name="kullanici_id" value="<?= $kullanici['id'] ?>">
                    <div class="form-control">
                        <label class="label"><span class="label-text">PDF Dosyası Seç</span></label>
                        <input type="file" name="dosya" accept="application/pdf" required
                            class="file-input file-input-bordered w-full max-w-md" />
                    </div>
                    <button type="submit" class="btn btn-success text-white mt-4"><i data-feather="upload"></i>
                        Yükle</button>
                </form>
            </div>
            <div>
                <a href="zimmet.php?id=<?= $kullanici['id'] ?>" class="btn btn-accent text-white mt-4 w-full">
                    <i data-feather="file-text"></i> Zimmet Formu Oluştur
                </a>
                   <a href="zimmet_eng.php?id=<?= $kullanici['id'] ?>" class="btn btn-accent text-white mt-4 w-full">
                    <i data-feather="file-text"></i> Zimmet Formu Oluştur (ENG)
                </a>
            </div>
        </div>



        <a href="index.php" class="btn btn-primary mt-6"><i data-feather="arrow-left"></i>Geri Dön</a>
    </div>

    <script>
        document.getElementById("yeniSatirEkle").addEventListener("click", function () {
            const ornek = document.querySelector(".demirbas-item");
            const klon = ornek.cloneNode(true);
            klon.querySelectorAll("input").forEach(input => input.value = "");
            klon.querySelectorAll("select").forEach(select => select.selectedIndex = 0);
            document.getElementById("demirbasListesi").appendChild(klon);
        });

        document.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-btn")) {
                const tumSatirlar = document.querySelectorAll(".demirbas-item");
                if (tumSatirlar.length > 1) {
                    e.target.closest(".demirbas-item").remove();
                } else {
                    alert("En az bir demirbaş formu olmalı.");
                }
            }
        });

        feather.replace();
    </script>
</body>

</html>