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
$section->addText("ZİMMET TESLİM FORMU", ['bold' => true, 'size' => 14], ['alignment' => 'center']);
$section->addTextBreak(1);

// Üst yazı
$section->addText("$tarih Tarihinde $ad_soyad TC Kimlik Nolu/Yabancı Kimlik Nolu , aşağıdaki araç gereç zimmetlenerek teslim edilmiştir.", ['size' => 11]);
$section->addTextBreak(1);

// Tablo
$table = $section->addTable([
    'borderSize' => 6,
    'borderColor' => '999999',
    'cellMargin' => 50,
]);

$table->addRow();
$table->addCell(2000)->addText('Tür');
$table->addCell(2000)->addText('Şirket');
$table->addCell(2000)->addText('Lokasyon');
$table->addCell(2000)->addText('Model');
$table->addCell(2000)->addText('Seri No');
$table->addCell(2000)->addText('Notlar');

foreach ($demirbaslar as $d) {
    $table->addRow();
    $table->addCell(2000)->addText($d['tur_ad'] ?? '');
    $table->addCell(2000)->addText($d['sirket_ad'] ?? '');
    $table->addCell(2000)->addText($d['lokasyon_ad'] ?? '');
    $table->addCell(2000)->addText($d['model'] ?? '');
    $table->addCell(2000)->addText($d['seri_no'] ?? '');
    $table->addCell(2000)->addText($d['notlar'] ?? '');
}

$section->addTextBreak(1);

// Taahhüt metni
$metin = <<<TEXT
Kullanım şartları ve şirketin zimmet kuralları hakkında bilgi verilerek yukarıdaki araç gereçleri teslim aldım.

Tarafıma zimmetlenen araç gereçleri uygun şekilde ve çalışma süresince kullanacağımı taahhüt ederim.

Bu araç/gereçlerin; zarar görmesi, arızalanması, kaybolması, çalınması halinde ilgili yerlere bilgi vereceğimi, bu araç ve gereçleri kullanım amacına uygun olarak özenli ve dikkatli şekilde kullanacağımı, bu araç ve gereçler üzerinde şahsi kusurum veya kastım sebebiyle oluşacak her türlü zararı (zarar görme, arıza, kayıp, çalıntı) tazmin edeceğimi, tazmin etmemem halinde işverenin ilgili zararı maaş ve varsa prim hak edişimden kesebileceğini, ayrıca işverenin bu sebeple İş Mevzuatı gereğince yapacağı işlem ve yaptırımları kabul ettiğimi beyan ederim.
TEXT;

$section->addTextBreak(1);
$section->addText($metin, ['size' => 11], ['alignment' => 'both']);
$section->addTextBreak(2);

// İmza alanları
$section->addText("Teslim Alan: $ad_soyad", ['bold' => true]);
$section->addText("İmza: ______________________");
$section->addTextBreak(1);
$section->addText("Teslim Eden: __________________", ['bold' => true]);
$section->addText("İmza: ______________________");

// Kaydet ve indir
$filename = "zimmet_formu_$ad_soyad.docx";
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save("php://output");
exit;
