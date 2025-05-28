<?php
session_start();
$conn = new mysqli("localhost", "root", "1234", "demirbas_takip");
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Girişler
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$departman_id = isset($_GET['departman_id']) ? (int) $_GET['departman_id'] : 0;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Departmanları al
$departmanlar = $conn->query("SELECT * FROM departmanlar")->fetch_all(MYSQLI_ASSOC);

// Kayıt sayısı sorgusu
$count_query = "SELECT COUNT(*) as total FROM kullanicilar WHERE 1";
if (!empty($search)) {
    $count_query .= " AND ad_soyad LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if ($filter == 'active') {
    $count_query .= " AND aktif = 1";
} elseif ($filter == 'inactive') {
    $count_query .= " AND aktif = 0";
}
if ($departman_id > 0) {
    $count_query .= " AND departman_id = $departman_id";
}
$total_records = $conn->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Kullanıcı sorgusu
$query = "SELECT * FROM kullanicilar WHERE 1";
if (!empty($search)) {
    $query .= " AND ad_soyad LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if ($filter == 'active') {
    $query .= " AND aktif = 1";
} elseif ($filter == 'inactive') {
    $query .= " AND aktif = 0";
}
if ($departman_id > 0) {
    $query .= " AND departman_id = $departman_id";
}
$query .= " ORDER BY ad_soyad ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Kullanıcılar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap');
    </style>
</head>

<body class="bg-gray-200 text-gray-800">

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold flex items-center gap-2"><i data-feather="users"></i>Kullanıcı Listesi</h1>
            <a href="ekle.php" class="btn btn-primary text-gray-100 flex items-center px-4 ml-4"><i
                    data-feather="user-plus"></i>Yeni Ekle</a>
        </div>

        <div class="max-w-48 mr-4 px-4 py-8">
         <a href="dashboard.php" class="btn btn-primary text-gray-100 flex items-center gap-1"><i data-feather="pie-chart"></i>Dashboard</a>
        </div>


        <!-- Arama ve Filtre -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="relative">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="İsim ile ara"
                    class="input input-bordered w-full bg-white ps-10" />
                <i data-feather="search" class="absolute top-3 left-3 text-gray-400"></i>
            </div>
            <select name="filter" class="select select-bordered w-full bg-white text-gray">
                <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Tüm Kullanıcılar</option>
                <option value="active" <?= $filter == 'active' ? 'selected' : '' ?>>Sadece Aktif</option>
                <option value="inactive" <?= $filter == 'inactive' ? 'selected' : '' ?>>Sadece Pasif</option>
            </select>
            <select name="departman_id" class="select select-bordered w-full bg-white text-gray">
                <option value="0">Tüm Departmanlar</option>
                <?php foreach ($departmanlar as $dep): ?>
                    <option value="<?= $dep['id'] ?>" <?= $dep['id'] == $departman_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dep['ad']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary w-full text-white"><i
                    data-feather="filter"></i>Filtrele</button>
        </form>

        <!-- Başarı Mesajı -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success shadow-lg mb-6 text-gray-100">
                <i data-feather="check"></i>
                <span><?= $_SESSION['success'] ?></span>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>


        <!-- Kullanıcı Kartları -->
        <div id="user-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 shadow-sm gap-6">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $dep_ad = '';
                    if ($row['departman_id']) {
                        $dep_result = $conn->query("SELECT ad FROM departmanlar WHERE id = {$row['departman_id']}");
                        $dep_ad = $dep_result->fetch_assoc()['ad'] ?? '';
                    }
                    ?>
                    <div class="card bg-white shadow-md border border-gray-200 hover:shadow-lg transition-all">
                        <div class="card-body">
                            <div class="flex justify-between items-center">
                                <h2 class="card-title text-lg font-semibold flex items-center gap-1">
                                    <?= htmlspecialchars($row['ad_soyad']) ?> <i data-feather="user"></i>
                                </h2>
                                <?php if ($row['aktif']): ?>
                                    <span class="badge badge-success text-white flex items-center gap-1 px-3"><i
                                            data-feather="check"></i>Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-error text-white flex items-center gap-1"><i
                                            data-feather="x"></i>Pasif</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500">Departman: <?= htmlspecialchars($dep_ad) ?></p>
                            <div class="mt-4 flex justify-between">
                                <a href="kullanici_detay.php?id=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-warning text-white">Detay</a>
                                <a href="kullanici_duzenle.php?id=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-info text-white">Düzenle</a>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <p class="col-span-3 text-center text-gray-500">Kayıt bulunamadı.</p>
            <?php endif; ?>
        </div>

        <!-- Sayfalama -->
        <div class="flex justify-center mt-10">
            <div class="join">
                <?php if ($page > 1): ?>
                    <a class="join-item btn"
                        href="?search=<?= urlencode($search) ?>&filter=<?= $filter ?>&departman_id=<?= $departman_id ?>&page=<?= $page - 1 ?>">«</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a class="join-item btn <?= $i == $page ? 'btn-active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&filter=<?= $filter ?>&departman_id=<?= $departman_id ?>&page=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a class="join-item btn"
                        href="?search=<?= urlencode($search) ?>&filter=<?= $filter ?>&departman_id=<?= $departman_id ?>&page=<?= $page + 1 ?>">»</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>

        feather.replace();
    </script>


</body>

</html>