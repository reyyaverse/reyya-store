<?php
include 'koneksi.php';

// Total Transaksi
$query_transaksi = mysqli_query($conn, "SELECT COUNT(*) AS total_transaksi FROM transaksi");
$total_transaksi = mysqli_fetch_assoc($query_transaksi)['total_transaksi'] ?? 0;

// Total Pendapatan
$query_pendapatan = mysqli_query($conn, "SELECT SUM(total_harga) AS total_pendapatan FROM transaksi");
$total_pendapatan = mysqli_fetch_assoc($query_pendapatan)['total_pendapatan'] ?? 0;

// Barang Terlaris
$query_terlaris = mysqli_query($conn, "
    SELECT b.nama_barang, SUM(t.jumlah) AS total_terjual
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    GROUP BY t.id_barang
    ORDER BY total_terjual DESC
    LIMIT 1
");
$data_terlaris = mysqli_fetch_assoc($query_terlaris);
$barang_terlaris = $data_terlaris['nama_barang'] ?? '-';
$total_terjual = $data_terlaris['total_terjual'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reyya Store</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <nav class="navbar-gradient fixed w-full shadow-md z-50 bg-gradient-to-r from-teal-700 to-teal-500 text-white">
    <div class="max-w-7xl px-10 py-4 flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold flex items-center space-x-2">
        🛍️ <span>Reyya Store</span>
      </a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="flex flex-col items-center justify-center text-center min-h-[60vh] bg-gradient-to-b from-white via-teal-100 to-teal-500 text-teal-900 pt-24 pb-12 rounded-b-3xl">
    <h1 class="text-3xl md:text-4xl font-extrabold mb-4 drop-shadow-lg">✨ WELCOME TO REYYA STORE MANAGEMENT ✨</h1>
    <p class="text-lg md:text-xl max-w-2xl text-gray-800">Smart way to manage <span class="font-semibold text-teal-700">products</span>, <span class="font-semibold text-teal-700">customers</span>, and <span class="font-semibold text-teal-700">transactions</span>.</p>
    <p class="mt-2 text-gray-700">Your all-in-one store management system.</p>
  </section>

  <!-- Menu Section -->
  <section class="max-w-6xl mx-auto py-12 px-6">
    <h2 class="text-center text-2xl font-bold mb-8 text-teal-800">📊 Manage Your Store</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

      <!-- Card Produk -->
      <a href="barang.php" class="block bg-white rounded-2xl p-8 shadow-md hover:shadow-lg hover:scale-105 transition transform text-center cursor-pointer">
        <div class="text-5xl mb-3">📦</div>
        <h3 class="text-xl font-semibold mb-3 text-gray-700">PRODUCTS</h3>
        <p class="text-gray-500">Manage and monitor your product inventory.</p>
      </a>

      <!-- Card Pembeli -->
      <a href="pembeli.php" class="block bg-white rounded-2xl p-8 shadow-md hover:shadow-lg hover:scale-105 transition transform text-center cursor-pointer">
        <div class="text-5xl mb-3">👥</div>
        <h3 class="text-xl font-semibold mb-3 text-gray-700">CUSTOMERS</h3>
        <p class="text-gray-500">View, add, or update your loyal customers.</p>
      </a>

      <!-- Card Transaksi -->
      <a href="transaksi.php" class="block bg-white rounded-2xl p-8 shadow-md hover:shadow-lg hover:scale-105 transition transform text-center cursor-pointer">
        <div class="text-5xl mb-3">🧾</div>
        <h3 class="text-xl font-semibold mb-3 text-gray-700">TRANSACTIONS</h3>
        <p class="text-gray-500">Check and manage all store transactions.</p>
      </a>

    </div>
  </section>

  <!-- Statistik Section -->
  <section class="bg-white py-12">
    <div class="max-w-6xl mx-auto px-6 text-center">
      <h2 class="text-2xl font-bold mb-8 text-teal-800">📈 Store Overview</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-gradient-to-br from-teal-100 to-teal-200 p-6 rounded-xl shadow">
          <h3 class="text-lg font-semibold text-gray-700">🧾 Total Transactions</h3>
          <p class="text-4xl font-bold text-teal-800 mt-2"><?= $total_transaksi ?></p>
        </div>

        <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 p-6 rounded-xl shadow">
          <h3 class="text-lg font-semibold text-gray-700">💰 Total Revenue</h3>
          <p class="text-4xl font-bold text-yellow-700 mt-2">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
        </div>

        <div class="bg-gradient-to-br from-blue-100 to-blue-200 p-6 rounded-xl shadow">
          <h3 class="text-lg font-semibold text-gray-700">🏆 Best Selling Item</h3>
          <p class="text-3xl font-bold text-blue-700 mt-2"><?= $barang_terlaris ?> (<?= $total_terjual ?> sold)</p>
        </div>

      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-100 border-t text-center py-4 mt-10 text-sm text-gray-600">
    © 2025 <span class="font-semibold text-teal-700">Reyya Store</span> | Smart Store Management
  </footer>

</body>
</html>
