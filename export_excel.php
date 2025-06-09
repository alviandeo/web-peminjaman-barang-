<?php
include 'config.php'; // Pastikan config.php sudah mengatur zona waktu dengan benar

// Fungsi untuk menerjemahkan nama hari dan bulan ke Bahasa Indonesia
function formatTanggalIndonesia($dateString) {
    // Tambahkan '0000-00-00 00:00:00' sebagai kondisi empty untuk tanggal kosong dari DB
    if (empty($dateString) || $dateString == '-' || $dateString == '0000-00-00 00:00:00') {
        return '-';
    }
    $timestamp = strtotime($dateString);
    $hari = date('w', $timestamp); // 0=Minggu, 1=Senin, dst.
    $bulan = date('n', $timestamp); // 1=Januari, dst.

    $namaHari = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];
    $namaBulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
        'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    return $namaHari[$hari] . ', ' . date('d', $timestamp) . ' ' . $namaBulan[$bulan] . ' ' . date('Y H:i', $timestamp);
}


// Offset minggu dari URL
$weekOffset = isset($_GET['weekOffset']) ? intval($_GET['weekOffset']) : 0;

// Dapatkan tanggal hari ini (akan mengikuti zona waktu WIB)
$today = date('Y-m-d');
$todayTime = strtotime($today);

// Cari hari dalam seminggu
$dayOfWeek = date('w', $todayTime); // 0 = Minggu, 1 = Senin, ..., 6 = Sabtu

// Hitung Senin dari minggu ini (jika Minggu, mundur 6 hari ke Senin sebelumnya)
$offsetToMonday = ($dayOfWeek == 0) ? -6 : 1 - $dayOfWeek;
$startOfWeek = strtotime("{$offsetToMonday} days", $todayTime) + ($weekOffset * 7 * 86400);
$endOfWeek = strtotime("+6 days", $startOfWeek);

// Format untuk query SQL
$startDate = date('Y-m-d 00:00:00', $startOfWeek);
$endDate = date('Y-m-d 23:59:59', $endOfWeek);

// Ambil data dari database peminjaman
// Kolom jenis_barang, dengan_siapa, dan dikembalikan_kepada kini diharapkan berisi teks langsung
// Perhatikan: $row['nama'] akan berisi nama peminjam awal ATAU nama pengembali
$query = mysqli_query($conn, "SELECT * FROM peminjaman_barang
    WHERE tanggal_peminjaman BETWEEN '$startDate' AND '$endDate'
    ORDER BY id DESC");

// Bagian ini tidak lagi diperlukan untuk menampilkan data peminjaman jika kolom `jenis_barang`
// di tabel `peminjaman_barang` sudah menyimpan teks. Namun, saya akan tetap mempertahankannya
// sesuai permintaan Anda "jangan ubah codingan yang lain".
$queryInventaris = mysqli_query($conn, "SELECT id, nama, seri FROM inventaris");
$jenisBarangList = [];
while ($rowInventaris = mysqli_fetch_assoc($queryInventaris)) {
    $display_name = $rowInventaris['nama'];
    if (!empty($rowInventaris['seri'])) {
        $display_name = $rowInventaris['nama'] . ' (' . $rowInventaris['seri'] . ')';
    }
    $jenisBarangList[$rowInventaris['id']] = $display_name;
}

// Pemetaan data untuk "Dengan Siapa"
// Ini juga tidak lagi mutlak diperlukan jika kolom `dengan_siapa` sudah menyimpan teks langsung.
$orangList = [
    1 => "Pak Trio", 2 => "Pak Budi", 3 => "Pak Yeyen", 4 => "Pak Aang",
    5 => "Pak Eko", 6 => "Pak Danang", 7 => "Pak Gatot"
];

// Set header untuk unduhan Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Logbook_Peminjaman_Barang_" . date('d-m-Y', $startOfWeek) . "_to_" . date('d-m-Y', $endOfWeek) . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// --- Output sebagai tabel HTML ---
echo "<table border='1'>"; // Tambahkan border agar lebih terlihat di Excel
echo "<tr>";
echo "<th>No</th>";
echo "<th>Nama Peminjam/Pengembali</th>"; // Label kolom disesuaikan agar lebih jelas
echo "<th>Jenis Barang</th>";
echo "<th>Dipinjam Dari</th>";
echo "<th>Status</th>";
echo "<th>Tanggal Peminjaman</th>";
echo "<th>Tanggal Pengembalian</th>";
echo "<th>Dikembalikan Kepada</th>";
echo "<th>Tanda Tangan Peminjam</th>";
echo "<th>Tanda Tangan Pengembali</th>";
echo "</tr>";

$no = 1;
while ($row = mysqli_fetch_assoc($query)) {
    // $row['nama'] akan otomatis berisi nama yang sudah diperbarui (peminjam atau pengembali)
    $namaTampil = htmlspecialchars($row['nama']);
    $jenisBarang = htmlspecialchars($row['jenis_barang']);
    $denganSiapa = htmlspecialchars($row['dengan_siapa']);

    if ($row['status'] == 'dipinjam') {
        $statusText = 'Masih Dipinjam';
        $tanggalPengembalian = '-';
        $dikembalikanKepada = '-'; // Belum dikembalikan, jadi kosong
        $tandaTanganPengembali = 'Belum ada';
    } else {
        $statusText = 'Sudah Kembali';
        $tanggalPengembalian = formatTanggalIndonesia($row['tanggal_pengembalian']);
        $dikembalikanKepada = htmlspecialchars($row['dikembalikan_kepada']);
        $tandaTanganPengembali = !empty($row['tanda_tangan_pengembalian']) ? 'Ada' : 'Tidak ada'; // Hanya info ada/tidak untuk Excel
    }

    $tanggalPeminjaman = formatTanggalIndonesia($row['tanggal_peminjaman']);
    $tandaTanganPeminjam = !empty($row['tanda_tangan_peminjaman']) ? 'Ada' : 'Tidak ada'; // Hanya info ada/tidak untuk Excel

    // Output baris data sebagai baris tabel
    echo "<tr>";
    echo "<td>{$no}</td>";
    echo "<td>{$namaTampil}</td>"; // Kolom ini akan menampilkan nama yang diperbarui
    echo "<td>{$jenisBarang}</td>";
    echo "<td>{$denganSiapa}</td>";
    echo "<td>{$statusText}</td>";
    echo "<td>{$tanggalPeminjaman}</td>";
    echo "<td>{$tanggalPengembalian}</td>";
    echo "<td>{$dikembalikanKepada}</td>";
    echo "<td>{$tandaTanganPeminjam}</td>";
    echo "<td>{$tandaTanganPengembali}</td>";
    echo "</tr>";
    $no++;
}

if ($no === 1) {
    echo "<tr><td colspan='10'>Tidak ada data peminjaman pada minggu ini.</td></tr>"; // Sesuaikan colspan
}

echo "</table>"; // Tutup tag tabel

exit; // Hentikan eksekusi setelah mengirim file
?>