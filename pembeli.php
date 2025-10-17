<?php
include 'koneksi.php';

$error = '';
$edit_mode = false;
$id_edit = null;
$cari = $_GET['cari'] ?? '';

// === Proses Tambah / Edit Pembeli ===
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_pembeli = ucwords(strtolower(trim($_POST['nama_pembeli'])));
    $alamat_pembeli = ucwords(strtolower(trim($_POST['alamat_pembeli'])));
    $no_handphone = trim($_POST['no_handphone']);

    if (isset($_POST['id_pembeli']) && !empty($_POST['id_pembeli'])) {
        $id_edit = $_POST['id_pembeli'];
        $query = "UPDATE pembeli 
                  SET nama_pembeli='$nama_pembeli', 
                      alamat_pembeli='$alamat_pembeli', 
                      no_handphone='$no_handphone'
                  WHERE id_pembeli='$id_edit'";
    } else {
        $query = "INSERT INTO pembeli (nama_pembeli, alamat_pembeli, no_handphone)
                  VALUES ('$nama_pembeli', '$alamat_pembeli', '$no_handphone')";
    }

    if (mysqli_query($conn, $query)) {
        header('Location: pembeli.php');
        exit;
    } else {
        $error = mysqli_error($conn);
    }
}

// === Proses Hapus Pembeli ===
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM pembeli WHERE id_pembeli='$id_hapus'");
    header('Location: pembeli.php');
    exit;
}

// === Ambil Data untuk Edit ===
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $result_edit = mysqli_query($conn, "SELECT * FROM pembeli WHERE id_pembeli='$id_edit'");
    $pembeli_edit = mysqli_fetch_assoc($result_edit);
    $edit_mode = true;
}

// === Query Data Pembeli (dengan pencarian) ===
$query_pembeli = "SELECT * FROM pembeli";
if (!empty($cari)) {
    $query_pembeli .= " WHERE nama_pembeli LIKE '%$cari%'";
}
$query_pembeli .= " ORDER BY id_pembeli ASC";
$result = mysqli_query($conn, $query_pembeli);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Customers</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <nav class="fixed w-full shadow-md z-50 bg-gradient-to-r from-teal-700 to-teal-500 text-white">
    <div class="w-full px-10 py-4 flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold flex items-center space-x-2">
        🛍️ <span>Reyya Store</span>
      </a>
      <div class="space-x-8 text-lg">
        <a href="index.php" class="hover:text-black">HOME</a>
        <a href="barang.php" class="hover:text-black">PRODUCT</a>
        <a href="pembeli.php" class="font-semibold text-black underline underline-offset-4">CUSTOMERS</a>
        <a href="transaksi.php" class="hover:text-black">TRANSACTIONS</a>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="flex flex-col items-center justify-center text-center bg-gradient-to-b from-white via-teal-100 to-teal-400 text-teal-900 pt-28 pb-12 rounded-b-3xl shadow-md">
    <h1 class="text-4xl font-extrabold mb-4 drop-shadow-lg">👥 CUSTOMER MANAGEMENT</h1>
  </section>

  <!-- Main Layout -->
  <main class="max-w-7xl mx-auto mt-10 mb-20 bg-white p-8 rounded-2xl shadow-lg border border-gray-100 flex flex-col lg:flex-row gap-8">

  <!-- FORM KIRI -->
  <div class="lg:w-1/3 bg-gradient-to-br from-teal-50 to-teal-100 p-6 rounded-xl shadow-md border border-gray-200 self-start">
    <h2 class="text-2xl font-bold text-teal-700 mb-6 text-center">
      <?= $edit_mode ? '✏️ Edit Customer' : '➕ Add New Customer' ?>
    </h2>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 mb-4 rounded text-center font-medium shadow"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-5">
      <input type="hidden" name="id_pembeli" value="<?= $edit_mode ? $pembeli_edit['id_pembeli'] : '' ?>">

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Customer Name</label>
        <input type="text" name="nama_pembeli" required 
               value="<?= $edit_mode ? htmlspecialchars($pembeli_edit['nama_pembeli']) : '' ?>"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Address</label>
        <textarea name="alamat_pembeli" rows="3" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"><?= $edit_mode ? htmlspecialchars($pembeli_edit['alamat_pembeli']) : '' ?></textarea>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number</label>
        <input type="text" name="no_handphone" required 
               value="<?= $edit_mode ? htmlspecialchars($pembeli_edit['no_handphone']) : '' ?>"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
      </div>

      <div class="flex justify-between items-center pt-4">
        <?php if ($edit_mode): ?>
          <a href="pembeli.php"
            class="px-5 py-2 rounded-lg bg-gray-200 border border-gray-300 text-gray-700 font-medium hover:bg-gray-300 transition">
            ❌ Cancel
          </a>
        <?php endif; ?>
        <button type="submit"
          class="px-6 py-2 rounded-lg bg-gradient-to-r from-teal-600 to-teal-500 text-white font-semibold shadow-md hover:scale-105 transition">
          💾 <?= $edit_mode ? 'Update' : 'Save' ?>
        </button>
      </div>
    </form>
  </div>

  <!-- TABEL KANAN -->
  <div class="flex-1">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold text-gray-700">📋 Customers List</h3>
      <form method="GET" action="pembeli.php" class="flex items-center gap-2">
        <input type="text" name="cari" placeholder="🔍 Search customer name..." value="<?= htmlspecialchars($cari) ?>"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
        <?php if (!empty($cari)): ?>
          <a href="pembeli.php" class="text-gray-500 hover:text-teal-600 text-sm font-medium">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-inner">
      <table class="min-w-full text-sm text-gray-700">
        <thead class="bg-gradient-to-r from-teal-600 to-teal-400 text-white">
          <tr>
            <th class="p-3 border">ID</th>
            <th class="p-3 border">Customer Name</th>
            <th class="p-3 border">Address</th>
            <th class="p-3 border">Phone</th>
            <th class="p-3 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr class="hover:bg-teal-50 transition">
                <td class="p-3 border text-center"><?= $row['id_pembeli'] ?></td>
                <td class="p-3 border text-center font-semibold"><?= $row['nama_pembeli'] ?></td>
                <td class="p-3 border text-center"><?= $row['alamat_pembeli'] ?></td>
                <td class="p-3 border text-center"><?= $row['no_handphone'] ?></td>
                <td class="p-3 border text-center flex justify-center gap-2">
                  <!-- Tombol Edit -->
                  <a href="?edit=<?= $row['id_pembeli'] ?>"
                    class="inline-flex items-center justify-center bg-amber-400/90 hover:bg-amber-500 text-white px-3 py-1.5 rounded-md shadow text-sm font-medium transition transform hover:scale-105">
                    Edit
                  </a>
                  <!-- Tombol Delete -->
                  <a href="?hapus=<?= $row['id_pembeli'] ?>"
                    onclick="return confirm('Are you sure you want to delete this customer?')"
                    class="inline-flex items-center justify-center bg-rose-500/90 hover:bg-rose-600 text-white px-3 py-1.5 rounded-md shadow text-sm font-medium transition transform hover:scale-105">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center py-5 text-gray-500 font-semibold">No customer data found.</td></tr>
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
