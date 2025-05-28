<?php
require 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$kullanici_id = $_GET['id'] ?? null;
if (!$kullanici_id) die("Kullanıcı ID bulunamadı.");

// Kullanıcıyı çek
$stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$kullanici_id]);
$kullanici = $stmt->fetch();

if (!$kullanici) die("Kullanıcı bulunamadı.");

$ad_soyad = $kullanici['ad_soyad'] ?? '________________';
$tarih = date('d/m/Y');

// Demirbaşları çek
$stmt2 = $conn->prepare("SELECT d.*, t.ad AS tur_ad, s.ad AS sirket_ad, l.ad AS lokasyon_ad
    FROM demirbaslar d
    LEFT JOIN turler t ON d.tur_id = t.id
    LEFT JOIN sirketler s ON d.sirket_id = s.id
    LEFT JOIN lokasyonlar l ON d.lokasyon_id = l.id
    WHERE d.kullanici_id = ?");
$stmt2->execute([$kullanici_id]);
$demirbaslar = $stmt2->fetchAll();

// PHPWord nesnesi
$phpWord = new PhpWord();
$section = $phpWord->addSection();

// Başlık
$section->addText("EQUIPMENT DELIVERY PROTOCOL", ['bold' => true, 'size' => 14], ['alignment' => 'center']);
$section->addTextBreak(1);

// Üst yazı
$section->addText("On date $tarih, holding the ID number/Foreign ID number ............... $ad_soyad the equipment listed below has been assigned and delivered ", ['size' => 11]);
$section->addTextBreak(1);

// Tablo
$table = $section->addTable([
    'borderSize' => 6,
    'borderColor' => '999999',
    'cellMargin' => 50,
]);

$table->addRow();
$table->addCell(2000)->addText('Type');
$table->addCell(2000)->addText('Company');
$table->addCell(2000)->addText('Location');
$table->addCell(2000)->addText('Model');
$table->addCell(2000)->addText('Serial No');

foreach ($demirbaslar as $d) {
    $table->addRow();
    $table->addCell(2000)->addText($d['tur_ad'] ?? '');
    $table->addCell(2000)->addText($d['sirket_ad'] ?? '');
    $table->addCell(2000)->addText($d['lokasyon_ad'] ?? '');
    $table->addCell(2000)->addText($d['model'] ?? '');
    $table->addCell(2000)->addText($d['seri_no'] ?? '');
}

$section->addTextBreak(1);

// Taahhüt metni
$metin = <<<TEXT
I have received the above-listed equipment after being informed about the terms of use and the company's assignment rules.

I hereby undertake to use the assigned equipment properly and exclusively during my working hours

In the event of damage, malfunction, loss, or theft of the equipment, I commit to informing the relevant parties. I also agree to use the equipment with care and in accordance with its intended purpose. Furthermore, I accept responsibility for any damage, malfunction, loss, or theft resulting from my negligence or deliberate actions and agree to compensate for such damages.

If I fail to provide compensation, I acknowledge that the employer has the right to deduct the equivalent amount from my salary and any applicable bonuses. Additionally, I accept that the employer may take necessary actions and apply sanctions in accordance with the Labor Law due to these circumstances.

TEXT;

$section->addTextBreak(1);
$section->addText($metin, ['size' => 11], ['alignment' => 'both']);
$section->addTextBreak(2);

// İmza alanları
$section->addText("Received by: $ad_soyad", ['bold' => true]);
$section->addText("Signature: ______________________");
$section->addTextBreak(1);
$section->addText("Delivered by: __________________", ['bold' => true]);
$section->addText("Signature: ______________________");

// Kaydet ve indir
$filename = "zimmet_formu_$kullanici_ad_soyad.docx";
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save("php://output");
exit;
