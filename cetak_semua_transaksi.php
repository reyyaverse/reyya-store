<?php
date_default_timezone_set('Asia/Jakarta');
include 'koneksi.php';

$dari = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

$query = "SELECT t.*, p.nama_pembeli, p.alamat_pembeli, p.no_handphone, b.nama_barang, b.harga_barang 
          FROM transaksi t
          LEFT JOIN pembeli p ON t.id_pembeli = p.id_pembeli
          LEFT JOIN barang b ON t.id_barang = b.id_barang";

if (!empty($dari) && !empty($sampai)) {
    $query .= " WHERE t.tanggal_transaksi BETWEEN '$dari' AND '$sampai'";
} elseif (!empty($dari)) {
    $query .= " WHERE t.tanggal_transaksi >= '$dari'";
} elseif (!empty($sampai)) {
    $query .= " WHERE t.tanggal_transaksi <= '$sampai'";
}

$query .= " ORDER BY t.id_transaksi ASC";
$result = mysqli_query($conn, $query);

// Calculate totals
$total_transaksi = 0;
$total_pendapatan = 0;
$data = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
        $total_transaksi++;
        $total_pendapatan += $row['total_harga'];
    }
}

// Get date range text for title
$periode = "";
if (!empty($dari) && !empty($sampai)) {
    $periode = "Period: " . date('d M Y', strtotime($dari)) . " - " . date('d M Y', strtotime($sampai));
} elseif (!empty($dari)) {
    $periode = "From: " . date('d M Y', strtotime($dari));
} elseif (!empty($sampai)) {
    $periode = "Until: " . date('d M Y', strtotime($sampai));
} else {
    $periode = "All Transactions";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All Transactions - Reyya Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                font-size: 12pt;
                line-height: 1.5;
            }

            @page {
                size: landscape;
                margin: 1.5cm;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #0d9488;
            color: white;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 p-6">
    <!-- Print Button -->
    <div class="flex justify-between mb-6 no-print">
        <button onclick="window.location.href='transaksi.php'" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg shadow-md flex items-center gap-2">
            ← Back
        </button>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow-md flex items-center gap-2">
            🖨️ Print
        </button>
    </div>

    <!-- Report Container -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
        <!-- Header -->
        <div class="text-center border-b border-gray-200 pb-6 mb-6">
            <div class="flex items-center justify-center gap-3">
                <span class="text-3xl">🛍️</span>
                <h1 class="text-3xl font-bold text-teal-700">Reyya Store</h1>
            </div>
            <h2 class="text-2xl font-semibold mt-4">Transaction Report</h2>
            <p class="text-gray-600"><?= htmlspecialchars($periode) ?></p>
            <p class="text-gray-500 mt-2">Generated on <?= date('F d, Y H:i') ?></p>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="bg-teal-50 border border-teal-100 rounded-lg p-6 text-center">
                <p class="text-gray-600 mb-1">🧾 Total Transactions</p>
                <p class="text-3xl font-bold text-teal-700"><?= $total_transaksi ?></p>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-lg p-6 text-center">
                <p class="text-gray-600 mb-1">💰 Total Revenue</p>
                <p class="text-3xl font-bold text-green-700">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Item</th>
                        <th>Date</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($data) > 0): ?>
                        <?php foreach ($data as $row): ?>
                            <tr class="border-b border-gray-200">
                                <td><?= htmlspecialchars($row['id_transaksi']) ?></td>
                                <td><?= htmlspecialchars($row['nama_pembeli'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['no_handphone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['nama_barang'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['tanggal_transaksi']) ?></td>
                                <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                <td class="font-semibold">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-gray-500">No transactions found for this period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold">
                        <td colspan="6" class="text-right">Total</td>
                        <td>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer -->
        <div class="text-center border-t border-gray-200 pt-6">
            <p class="font-semibold text-teal-700">Reyya Store - Smart Store Management</p>
            <p class="text-gray-500 mt-2">This is an automatically generated report.</p>
        </div>
    </div>

    <!-- Print Instructions -->
    <div class="mt-6 bg-yellow-50 border border-yellow-200 p-4 rounded-lg text-center no-print">
        <p class="text-yellow-800">Press the Print button above or use Ctrl+P / Cmd+P to print this report</p>
    </div>
</body>

</html>