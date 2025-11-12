<?php

// 1. ATUR PATH FOLDER YANG MAU DI-ZIP DI SINI
$dirPath = '/var/www/localhost/htdocs/admin/modules/bibliography/File/MARC/Lint/_quarantine';

// Nama file zip yang akan di-download
$zipFileName = 'backup_quarantine_' . date('Y-m-d') . '.zip';
$zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

// Cek jika folder ada
if (!is_dir($dirPath)) {
    die("Error: Direktori tidak ditemukan di path: " . htmlspecialchars($dirPath));
}

// Inisialisasi ZipArchive
$zip = new ZipArchive();
if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Error: Tidak bisa membuat file ZIP di " . htmlspecialchars($zipFilePath));
}

// Membuat iterator untuk scan direktori secara rekursif (termasuk sub-direktori)
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

echo "Memulai proses zip... <br>";

foreach ($files as $file) {
    // Dapatkan path file yang sebenarnya dan path relatif di dalam folder
    $realPath = $file->getRealPath();
    $relativePath = substr($realPath, strlen($dirPath) + 1);

    if ($file->isDir()) {
        // Tambahkan direktori kosong ke zip
        $zip->addEmptyDir($relativePath);
    } else {
        // Tambahkan file ke zip
        $zip->addFile($realPath, $relativePath);
    }
    echo "Menambahkan: " . htmlspecialchars($relativePath) . "<br>";
}

// Tutup file zip
$zip->close();

echo "Proses zip selesai. Memulai download...";

// 2. PAKSA DOWNLOAD FILE ZIP
if (file_exists($zipFilePath)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
    header('Content-Length: ' . filesize($zipFilePath));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Hapus buffer output sebelumnya
    ob_clean();
    flush();

    // Baca file dan kirim ke browser
    readfile($zipFilePath);

    // 3. HAPUS FILE ZIP SEMENTARA DI SERVER
    unlink($zipFilePath);
    exit;
} else {
    echo "Error: File ZIP yang sudah dibuat tidak ditemukan.";
}
?>
