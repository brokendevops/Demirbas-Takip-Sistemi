<?php
require 'db.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_id = $_POST['kullanici_id'] ?? null;

    if (!$kullanici_id) {
        header("Location: index.php?error=Kullanıcı seçilmedi.");
        exit;
    }

    $tur_ids         = $_POST['tur_id'] ?? [];
    $sirket_ids      = $_POST['sirket_id'] ?? [];
    $lokasyon_ids    = $_POST['lokasyon_id'] ?? [];
    $teslim_eden_ids = $_POST['teslim_eden_id'] ?? [];
    $modeller        = $_POST['model'] ?? [];
    $gsm_nolar       = $_POST['gsm_no'] ?? [];
    $imei_1_nolar    = $_POST['imei_1_no'] ?? [];
    $iccid_1_nolar   = $_POST['iccid_1_no'] ?? [];
    $imei_2_nolar    = $_POST['imei_2_no'] ?? [];
    $seri_nolar      = $_POST['seri_no'] ?? [];
    $notlar          = $_POST['notlar'] ?? [];

    try {
        $stmt = $conn->prepare("INSERT INTO demirbaslar (
            kullanici_id, tur_id, sirket_id, lokasyon_id, teslim_eden_id,
            model, gsm_no, imei_1_no, iccid_1_no, imei_2_no, seri_no, notlar
        ) VALUES (
            :kullanici_id, :tur_id, :sirket_id, :lokasyon_id, :teslim_eden_id,
            :model, :gsm_no, :imei_1_no, :iccid_1_no, :imei_2_no, :seri_no, :notlar
        )");

        for ($i = 0; $i < count($tur_ids); $i++) {
            $stmt->execute([
                'kullanici_id'     => $kullanici_id,
                'tur_id'           => $tur_ids[$i],
                'sirket_id'        => $sirket_ids[$i],
                'lokasyon_id'      => $lokasyon_ids[$i],
                'teslim_eden_id'   => $teslim_eden_ids[$i],
                'model'            => trim($modeller[$i]),
                'gsm_no'           => trim($gsm_nolar[$i]),
                'imei_1_no'          => trim($imei_1_nolar[$i]),
                'iccid_1_no'          => trim($iccid_1_nolar[$i]),
                'imei_2_no'           => trim($imei_2_nolar[$i]),
                'seri_no'          => trim($seri_nolar[$i]),
                'notlar'           => trim($notlar[$i]),
            ]);
        }

       $_SESSION['success'] = "Demirbaşlar başarıyla eklendi!";
    header("Location: kullanici_detay.php?id=" . $demirbas['kullanici_id']);
    exit;

    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
    }
}
?>
