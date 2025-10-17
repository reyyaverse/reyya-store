<?php
include 'koneksi.php';

$error = '';
$edit_mode = false;
$id_edit = null;
$dari = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

// === Ambil Data Pembeli & Barang ===
$pembeli_result = mysqli_query($conn, "SELECT * FROM pembeli");
$barang_result = mysqli_query($conn, "SELECT * FROM barang");

// === Proses Tambah / Edit Transaksi ===
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id_pembeli = $_POST['id_pembeli'];
  $id_barang = $_POST['id_barang'];
  $jumlah = $_POST['jumlah'];
  $tanggal_transaksi = date('Y-m-d H:i:s');

  $barang_query = mysqli_query($conn, "SELECT harga_barang, stok_barang FROM barang WHERE id_barang='$id_barang'");
  $barang = mysqli_fetch_assoc($barang_query);

  if (!$barang) {
    $error = "⚠️ Product not found!";
  } else {
    $stok_sekarang = $barang['stok_barang'];
    $harga = $barang['harga_barang'];
    $total_harga = $harga * $jumlah;

    if (isset($_POST['id_transaksi']) && !empty($_POST['id_transaksi'])) {
      // MODE EDIT
      $id_edit = $_POST['id_transaksi'];
      $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi='$id_edit'"));
      $stok_kembali = $stok_sekarang + $old['jumlah'];

      if ($stok_kembali < $jumlah) {
        $error = "⚠️ Insufficient stock! Available: $stok_kembali";
      } else {
        mysqli_query($conn, "UPDATE barang SET stok_barang = $stok_kembali - $jumlah WHERE id_barang='$id_barang'");
        mysqli_query($conn, "UPDATE transaksi SET 
            id_pembeli='$id_pembeli', id_barang='$id_barang', 
            jumlah='$jumlah', total_harga='$total_harga', 
            tanggal_transaksi='$tanggal_transaksi'
            WHERE id_transaksi='$id_edit'");
        header("Location: transaksi.php");
        exit;
      }
    } else {
      // MODE TAMBAH
      if ($stok_sekarang < $jumlah) {
        $error = "⚠️ Insufficient stock! Available: $stok_sekarang";
      } else {
        mysqli_query($conn, "INSERT INTO transaksi (id_pembeli, id_barang, jumlah, total_harga, tanggal_transaksi)
                             VALUES ('$id_pembeli', '$id_barang', '$jumlah', '$total_harga', '$tanggal_transaksi')");
        mysqli_query($conn, "UPDATE barang SET stok_barang = stok_barang - $jumlah WHERE id_barang='$id_barang'");
        header("Location: transaksi.php");
        exit;
      }
    }
  }
}

// === Proses Hapus ===
if (isset($_GET['hapus'])) {
  $id_hapus = $_GET['hapus'];
  $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi='$id_hapus'"));
  if ($old) {
    mysqli_query($conn, "UPDATE barang SET stok_barang = stok_barang + {$old['jumlah']} WHERE id_barang='{$old['id_barang']}'");
    mysqli_query($conn, "DELETE FROM transaksi WHERE id_transaksi='$id_hapus'");
  }
  header("Location: transaksi.php");
  exit;
}

// === Ambil Data untuk Edit ===
if (isset($_GET['edit'])) {
  $id_edit = $_GET['edit'];
  $transaksi_edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi='$id_edit'"));
  $edit_mode = true;
}

// === Query Data Transaksi ===
$query = "SELECT t.*, p.nama_pembeli, p.no_handphone, b.nama_barang 
          FROM transaksi t
          LEFT JOIN pembeli p ON t.id_pembeli = p.id_pembeli
          LEFT JOIN barang b ON t.id_barang = b.id_barang";

if (!empty($dari) && !empty($sampai)) {
  $query .= " WHERE DATE(t.tanggal_transaksi) BETWEEN '$dari' AND '$sampai'";
} elseif (!empty($dari)) {
  $query .= " WHERE DATE(t.tanggal_transaksi) >= '$dari'";
} elseif (!empty($sampai)) {
  $query .= " WHERE DATE(t.tanggal_transaksi) <= '$sampai'";
}
$query .= " ORDER BY t.id_transaksi ASC";
$result = mysqli_query($conn, $query);

$filter_url = (!empty($dari) || !empty($sampai)) ? '?dari=' . urlencode($dari) . '&sampai=' . urlencode($sampai) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Transaction Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <nav class="fixed w-full shadow-md z-50 bg-gradient-to-r from-teal-700 to-teal-500 text-white">
    <div class="w-full px-10 py-4 flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold flex items-center space-x-2">🛍️ <span>Reyya Store</span></a>
      <div class="space-x-8 text-lg">
        <a href="index.php" class="hover:text-black">HOME</a>
        <a href="barang.php" class="hover:text-black">PRODUCT</a>
        <a href="pembeli.php" class="hover:text-black">CUSTOMERS</a>
        <a href="transaksi.php" class="font-semibold text-black underline underline-offset-4">TRANSACTIONS</a>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="text-center bg-gradient-to-b from-white via-teal-100 to-teal-400 text-teal-900 pt-28 pb-12 rounded-b-3xl shadow-md">
    <h1 class="text-4xl font-extrabold mb-4 drop-shadow-lg">💳 TRANSACTION MANAGEMENT</h1>
  </section>

  <!-- Layout -->
  <main class="max-w-7xl mx-auto mt-10 mb-20 bg-white p-8 rounded-2xl shadow-lg border border-gray-100 flex flex-col lg:flex-row gap-8">

  <!-- FORM KIRI -->
  <div class="lg:w-1/3 bg-gradient-to-br from-teal-50 to-teal-100 p-6 rounded-xl shadow-md border border-gray-200 self-start">
    <h2 class="text-2xl font-bold text-teal-700 mb-6 text-center">
      <?= $edit_mode ? '✏️ Edit Transaction' : '➕ Add New Transaction' ?>
    </h2>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 mb-4 rounded text-center font-medium shadow"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-5">
      <input type="hidden" name="id_transaksi" value="<?= $edit_mode ? $transaksi_edit['id_transaksi'] : '' ?>">

      <!-- Pembeli -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Buyer</label>
        <select name="id_pembeli" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500">
          <option value="">-- Choose Buyer --</option>
          <?php 
          mysqli_data_seek($pembeli_result, 0);
          while ($p = mysqli_fetch_assoc($pembeli_result)): ?>
            <option value="<?= $p['id_pembeli'] ?>" <?= $edit_mode && $p['id_pembeli'] == $transaksi_edit['id_pembeli'] ? 'selected' : '' ?>>
              <?= $p['nama_pembeli'] ?> (📞 <?= $p['no_handphone'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Barang -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Product</label>
        <select name="id_barang" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500">
          <option value="">-- Choose Item --</option>
          <?php 
          mysqli_data_seek($barang_result, 0);
          while ($b = mysqli_fetch_assoc($barang_result)): ?>
            <option value="<?= $b['id_barang'] ?>" <?= $edit_mode && $b['id_barang'] == $transaksi_edit['id_barang'] ? 'selected' : '' ?>>
              <?= $b['nama_barang'] ?> — Rp<?= number_format($b['harga_barang'], 0, ',', '.') ?> (Stock: <?= $b['stok_barang'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Jumlah -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Quantity</label>
        <input type="number" name="jumlah" min="1" required value="<?= $edit_mode ? $transaksi_edit['jumlah'] : '' ?>"
          class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500">
      </div>

      <div class="flex justify-between items-center pt-4">
        <?php if ($edit_mode): ?>
          <a href="transaksi.php" class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition">❌ Cancel</a>
        <?php endif; ?>
        <button type="submit" class="px-6 py-2 rounded-lg bg-gradient-to-r from-teal-600 to-teal-500 text-white font-semibold shadow-md hover:scale-105 transition">
          💾 <?= $edit_mode ? 'Update' : 'Save' ?>
        </button>
      </div>
    </form>
  </div>

  <!-- TABEL KANAN -->
  <div class="flex-1">
    <div class="flex flex-wrap justify-between items-end gap-4 mb-5 bg-gray-100 p-4 rounded-lg shadow-sm">
      <!-- Filter -->
      <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">From</label>
          <input type="date" name="dari" value="<?= $dari ?>" class="border p-2 rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">To</label>
          <input type="date" name="sampai" value="<?= $sampai ?>" class="border p-2 rounded-lg">
        </div>
        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg shadow">🔍 Filter</button>
        <?php if (!empty($dari) || !empty($sampai)): ?>
          <a href="transaksi.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg shadow">♻️ Reset</a>
        <?php endif; ?>
      </form>

      <!-- Print All -->
      <a href="cetak_semua_transaksi.php<?= $filter_url ?>" target="_blank"
        class="bg-teal-500 hover:bg-teal-600 text-white px-5 py-2 rounded-lg shadow-md flex items-center gap-2 transition">
        🖨️ Print All
      </a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-inner">
      <table class="min-w-full text-sm text-gray-700 border-collapse">
        <thead class="bg-gradient-to-r from-teal-600 to-teal-400 text-white">
          <tr>
            <th class="p-3 border">ID</th>
            <th class="p-3 border">Buyer</th>
            <th class="p-3 border">Phone</th>
            <th class="p-3 border">Item</th>
            <th class="p-3 border">Date</th>
            <th class="p-3 border">Quantity</th>
            <th class="p-3 border">Total</th>
            <th class="p-3 border">Actions</th>
          </tr>
        </thead>
        <tbody>
  <?php if (mysqli_num_rows($result) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <tr class="hover:bg-teal-50 transition">
        <td class="p-3 border text-center"><?= $row['id_transaksi'] ?></td>
        <td class="p-3 border text-center font-semibold"><?= $row['nama_pembeli'] ?></td>
        <td class="p-3 border text-center"><?= $row['no_handphone'] ?></td>
        <td class="p-3 border text-center"><?= $row['nama_barang'] ?></td>
        <td class="p-3 border text-center"><?= $row['tanggal_transaksi'] ?></td>
        <td class="p-3 border text-center"><?= $row['jumlah'] ?></td>
        <td class="p-3 border text-center text-green-600 font-semibold">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
        <td class="p-3 border text-center">
          <div class="flex flex-col items-center gap-2">
            <!-- Edit -->
            <a href="?edit=<?= $row['id_transaksi'] ?>" 
              class="inline-flex items-center justify-center bg-amber-400/90 hover:bg-amber-500 text-white px-3 py-1.5 rounded-md shadow-md text-xs font-medium transition transform hover:scale-105">
              Edit
            </a>
            <!-- Delete -->
            <a href="?hapus=<?= $row['id_transaksi'] ?>" 
              onclick="return confirm('Are you sure you want to delete this transaction?')" 
              class="inline-flex items-center justify-center bg-rose-500/90 hover:bg-rose-600 text-white px-3 py-1.5 rounded-md shadow-md text-xs font-medium transition transform hover:scale-105">
              Delete
            </a>
            <!-- Print -->
            <a href="cetak_transaksi.php?id=<?= $row['id_transaksi'] ?>" target="_blank" 
              class="inline-flex items-center justify-center bg-cyan-500/90 hover:bg-cyan-600 text-white px-3 py-1.5 rounded-md shadow-md text-xs font-medium transition transform hover:scale-105">
              Print
            </a>
          </div>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="8" class="text-center py-5 text-gray-500 font-semibold">No transactions found.</td></tr>
  <?php endif; ?>
</tbody>
      </table>
    </div>
  </div>
</main>


  <footer class="bg-gray-100 border-t text-center py-5 mt-auto text-sm text-gray-600">
    © 2025 <span class="font-semibold text-teal-700">Reyya Store</span> | Smart Store Management
  </footer>

</body>
</html>
