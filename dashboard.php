<?php
require 'db.php';

// Kartlar
$totalUsers = $conn->query("SELECT COUNT(*) FROM kullanicilar")->fetchColumn();
$activeUsers = $conn->query("SELECT COUNT(*) FROM kullanicilar WHERE aktif = 1")->fetchColumn();
$totalAssets = $conn->query("SELECT COUNT(*) FROM demirbaslar")->fetchColumn();
$today = date('Y-m-d');
$todayUsers = $conn->query("SELECT COUNT(*) FROM kullanicilar WHERE DATE(eklenme_tarihi) = '$today'")->fetchColumn();
$todayAssets = $conn->query("SELECT COUNT(*) FROM demirbaslar WHERE DATE(eklenme_tarihi) = '$today'")->fetchColumn();

// Aylık grafik
$monthlyData = $conn->query("
    SELECT DATE_FORMAT(eklenme_tarihi, '%Y-%m') AS ay, COUNT(*) AS sayi
    FROM demirbaslar
    GROUP BY ay
    ORDER BY ay DESC
    LIMIT 6
")->fetchAll();

// Şirket bazlı
$companyData = $conn->query("
    SELECT s.ad AS sirket, COUNT(*) AS sayi
    FROM demirbaslar d
    JOIN sirketler s ON d.sirket_id = s.id
    GROUP BY d.sirket_id
")->fetchAll();

// Tür bazlı aktif vs toplam
$turData = $conn->query("
    SELECT t.ad AS tur, 
           COUNT(d.id) AS toplam,
           SUM(CASE WHEN k.aktif = 1 THEN 1 ELSE 0 END) AS aktif
    FROM demirbaslar d
    LEFT JOIN turler t ON d.tur_id = t.id
    LEFT JOIN kullanicilar k ON d.kullanici_id = k.id
    GROUP BY d.tur_id
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-base-100 text-base-content">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">📊 Yönetim Paneli</h1>
            <label class="swap swap-rotate">
                <input type="checkbox" class="theme-controller" value="dark"/>
                <svg class="swap-on fill-current w-6 h-6" data-feather="moon"></svg>
                <svg class="swap-off fill-current w-6 h-6" data-feather="sun"></svg>
            </label>
        </div>

        <!-- Kartlar -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <a href="index.php" class="card bg-base-200 shadow hover:shadow-lg transition">
                <div class="card-body flex items-center gap-4">
                    <i data-feather="users" class="text-primary w-6 h-6"></i>
                    <div>
                        <p class="text-sm">Toplam Kullanıcı</p>
                        <h2 class="text-xl font-bold"><?= $totalUsers ?></h2>
                    </div>
                </div>
            </a>
            <a href="index.php?filter=active" class="card bg-base-200 shadow hover:shadow-lg transition">
                <div class="card-body flex items-center gap-4">
                    <i data-feather="user-check" class="text-success w-6 h-6"></i>
                    <div>
                        <p class="text-sm">Aktif Kullanıcı</p>
                        <h2 class="text-xl font-bold"><?= $activeUsers ?></h2>
                    </div>
                </div>
            </a>
            <a href="index.php" class="card bg-base-200 shadow hover:shadow-lg transition">
                <div class="card-body flex items-center gap-4">
                    <i data-feather="cpu" class="text-info w-6 h-6"></i>
                    <div>
                        <p class="text-sm">Toplam Demirbaş</p>
                        <h2 class="text-xl font-bold"><?= $totalAssets ?></h2>
                    </div>
                </div>
            </a>
            <div class="card bg-base-200 shadow">
                <div class="card-body flex items-center gap-4">
                    <i data-feather="calendar" class="text-warning w-6 h-6"></i>
                    <div>
                        <p class="text-sm">Bugün Eklenenler</p>
                        <h2 class="text-xl font-bold"><?= $todayUsers ?> kullanıcı / <?= $todayAssets ?> demirbaş</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafikler -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-base-200 p-6 rounded-box shadow">
                <h3 class="text-lg font-bold mb-4">📈 Aylık Demirbaş Ekleme</h3>
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
            <div class="bg-base-200 p-6 rounded-box shadow">
                <h3 class="text-lg font-bold mb-4">🏢 Şirket Bazlı Dağılım</h3>
                <canvas id="companyChart" height="200"></canvas>
            </div>
        </div>

        <!-- Tür Bazlı Grafik -->
        <div class="bg-base-200 p-6 rounded-box shadow mt-6">
            <h3 class="text-lg font-bold mb-4">🧩 Tür Bazlı Demirbaş Durumu</h3>
            <canvas id="turChart" height="200"></canvas>
        </div>

        <a href="index.php" class="btn btn-primary mt-6"><i data-feather="arrow-left"></i> Geri Dön</a>
    </div>

    <script>
        feather.replace();

        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_reverse(array_column($monthlyData, 'ay'))) ?>,
                datasets: [{
                    label: 'Demirbaş Sayısı',
                    data: <?= json_encode(array_reverse(array_column($monthlyData, 'sayi'))) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        const companyCtx = document.getElementById('companyChart').getContext('2d');
        new Chart(companyCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($companyData, 'sirket')) ?>,
                datasets: [{
                    label: 'Demirbaşlar',
                    data: <?= json_encode(array_column($companyData, 'sayi')) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        const turCtx = document.getElementById('turChart').getContext('2d');
        new Chart(turCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($turData, 'tur')) ?>,
                datasets: [
                    {
                        label: 'Toplam',
                        data: <?= json_encode(array_column($turData, 'toplam')) ?>,
                        backgroundColor: 'rgba(96, 165, 250, 0.6)',
                        borderColor: 'rgba(96, 165, 250, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Aktif Kullanımda',
                        data: <?= json_encode(array_column($turData, 'aktif')) ?>,
                        backgroundColor: 'rgba(34, 197, 94, 0.6)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
