<?php
include 'koneksi.php';

// Check if ID exists in the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to transactions page
if ($id <= 0) {
    header("Location: transaksi.php");
    exit;
}

// Query to get transaction data with related information
$query = "SELECT t.*, p.nama_pembeli, p.alamat_pembeli, p.no_handphone, b.nama_barang, b.harga_barang
          FROM transaksi t
          LEFT JOIN pembeli p ON t.id_pembeli = p.id_pembeli
          LEFT JOIN barang b ON t.id_barang = b.id_barang
          WHERE t.id_transaksi = $id";

$result = mysqli_query($conn, $query);

// Check if transaction exists
if (mysqli_num_rows($result) == 0) {
    echo "Transaction not found!";
    exit;
}

$transaksi = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Transaction #<?= $id ?> - Reyya Store</title>
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
                size: A4;
                margin: 1.5cm;
            }

            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 p-8">
    <!-- Print Button -->
    <div class="flex justify-between mb-8 no-print">
        <button onclick="window.location.href='transaksi.php'" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg shadow-md flex items-center gap-2">
            ← Back
        </button>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow-md flex items-center gap-2">
            🖨️ Print
        </button>
    </div>

    <!-- Receipt Container -->
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border border-gray-200 p-8">
        <!-- Header -->
        <div class="border-b border-gray-200 pb-6 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🛍️</span>
                    <h1 class="text-2xl font-bold text-teal-700">Reyya Store</h1>
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-semibold">Transaction Receipt</h2>
                    <p class="text-gray-500">Invoice #<?= $transaksi['id_transaksi'] ?></p>
                </div>
            </div>
        </div>

        <!-- Customer & Transaction Info -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <!-- Customer Info -->
            <div>
                <h3 class="font-semibold text-gray-600 mb-2">Customer Information</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="font-bold text-lg"><?= htmlspecialchars($transaksi['nama_pembeli'] ?? 'N/A') ?></p>
                    <p class="text-gray-700"><?= htmlspecialchars($transaksi['alamat_pembeli'] ?? 'N/A') ?></p>
                    <p class="text-gray-700"><?= htmlspecialchars($transaksi['no_handphone'] ?? 'N/A') ?></p>
                </div>
            </div>

            <!-- Transaction Info -->
            <div>
                <h3 class="font-semibold text-gray-600 mb-2">Transaction Details</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between">
                        <p class="text-gray-700">Transaction Date:</p>
                        <p class="font-semibold"><?= htmlspecialchars($transaksi['tanggal_transaksi']) ?></p>
                    </div>
                    <div class="flex justify-between">
                        <p class="text-gray-700">Transaction ID:</p>
                        <p class="font-semibold"><?= htmlspecialchars($transaksi['id_transaksi']) ?></p>
                    </div>
                    <div class="flex justify-between">
                        <p class="text-gray-700">Payment Method:</p>
                        <p class="font-semibold">Cash</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="mb-8">
            <h3 class="font-semibold text-gray-600 mb-2">Product Details</h3>
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 p-3 text-left">Product</th>
                        <th class="border border-gray-300 p-3 text-right">Unit Price</th>
                        <th class="border border-gray-300 p-3 text-right">Quantity</th>
                        <th class="border border-gray-300 p-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-300 p-3"><?= htmlspecialchars($transaksi['nama_barang'] ?? 'N/A') ?></td>
                        <td class="border border-gray-300 p-3 text-right">Rp <?= number_format($transaksi['harga_barang'] ?? 0, 0, ',', '.') ?></td>
                        <td class="border border-gray-300 p-3 text-right"><?= htmlspecialchars($transaksi['jumlah']) ?></td>
                        <td class="border border-gray-300 p-3 text-right font-semibold">Rp <?= number_format($transaksi['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="border border-gray-300 p-3 text-right font-semibold">Total Amount</td>
                        <td class="border border-gray-300 p-3 text-right font-bold text-green-700">Rp <?= number_format($transaksi['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes & Terms -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="font-semibold text-gray-600 mb-2">Notes</h3>
                <p class="text-gray-600 bg-gray-50 p-4 rounded-lg">
                    Thank you for shopping at Reyya Store! We appreciate your business.
                </p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-600 mb-2">Terms & Conditions</h3>
                <ul class="list-disc list-inside text-gray-600 bg-gray-50 p-4 rounded-lg">
                    <li>All returned items must be in original condition</li>
                    <li>Returns accepted within 7 days of purchase</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center border-t border-gray-200 pt-6">
            <p class="font-semibold text-teal-700 mt-2">Reyya Store - Smart Store Management</p>
            <p class="text-gray-500">Printed on <?= date('F d, Y') ?></p>
        </div>
    </div>

    <!-- Print Instructions -->
    <div class="max-w-4xl mx-auto mt-8 bg-yellow-50 border border-yellow-200 p-4 rounded-lg text-center no-print">
        <p class="text-yellow-800">Press the Print button above or use Ctrl+P / Cmd+P to print this receipt</p>
    </div>

    <script>
        // Auto focus the print button for better UX
        window.onload = function() {
            document.querySelectorAll('button')[1].focus();
        };
    </script>
</body>

</html>