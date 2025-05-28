<?php
// db_connect.php

$host = 'localhost';       // Sunucu adı
$dbname = 'demirbas_takip';   // Veritabanı adı
$username = 'root';        // Kullanıcı adı
$password = '1234';            // Şifre (boşsa buraya boş bırak)

// PDO bağlantısı için DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Hata modunu istisna olarak ayarla
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Varsayılan fetch tipi dizi olarak ayarla
    PDO::ATTR_EMULATE_PREPARES => false,              // Gerçek prepared statement kullan
];

try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Bağlantı başarısızsa hata mesajı göster
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>