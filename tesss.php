<?php
// ---------------------------------------------------------------------
// --- KONFIGURASI ---
// ---------------------------------------------------------------------

// Folder yang ingin Anda zip (Ganti dengan path lengkap)
$folder_to_zip = '/var/www/html';

// Nama file zip yang akan dihasilkan
$zip_file_name = 'backup_website.zip';

// Lokasi di mana file zip akan disimpan
// (Disarankan 1 level di atas folder yang di-zip agar rapi)
$zip_path = dirname($folder_to_zip) . '/' . $zip_file_name;

// ---------------------------------------------------------------------
// --- FUNGSI REKURSIF ---
// ---------------------------------------------------------------------

/**
 * Menambahkan file & folder ke arsip Zip secara rekursif.
 * @param string $folder Path ke folder yang akan dipindai
 * @param ZipArchive $zip Objek ZipArchive
 * @param int $exclusive_length Panjang path yang akan dipotong
 */
function add_folder_to_zip($folder, &$zip, $exclusive_length) {
    $handle = opendir($folder);
    while (false !== $f = readdir($handle)) {
        if ($f != '.' && $f != '..') {
            $filePath = "$folder/$f";
            // Path di dalam zip (misal: 'css/style.css')
            $localPath = substr($filePath, $exclusive_length); 
            
            if (is_file($filePath)) {
                $zip->addFile($filePath, $localPath);
            } elseif (is_dir($filePath)) {
                $zip->addEmptyDir($localPath);
                add_folder_to_zip($filePath, $zip, $exclusive_length);
            }
        }
    }
    closedir($handle);
}

// ---------------------------------------------------------------------
// --- PROSES UTAMA ---
// ---------------------------------------------------------------------

echo "<h1>Proses Zipping</h1>";

// Coba atasi batas waktu eksekusi (mungkin gagal di shared hosting)
set_time_limit(0);
ini_set('memory_limit', '512M');

// Cek apakah ekstensi Zip diinstal
if (!class_exists('ZipArchive')) {
    die('<strong style="color:red;">Error: Class ZipArchive tidak ditemukan. Ekstensi PHP Zip belum diinstal di server Anda.</strong>');
}

// Cek apakah folder sumber ada
$path_to_zip = realpath($folder_to_zip);
if ($path_to_zip === false || !is_dir($path_to_zip)) {
    die('<strong style="color:red;">Error: Folder tidak ditemukan: ' . htmlspecialchars($folder_to_zip) . '</strong>');
}

// Buat objek Zip
$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die('<strong style="color:red;">Error: Gagal membuat file zip di ' . htmlspecialchars($zip_path) . '</strong>');
}

echo "Mulai proses zip... (Ini bisa makan waktu sangat lama)<br>";
echo "<b>Folder Sumber:</b> " . htmlspecialchars($path_to_zip) . "<br>";
echo "<b>File Output:</b> " . htmlspecialchars($zip_path) . "<br><br>";
flush(); // Kirim output ini ke browser sekarang

// Tentukan panjang path yang akan dipotong agar isi zip rapi
$exclusive_length = strlen($path_to_zip) + 1; // +1 untuk slash

// Panggil fungsi rekursif
add_folder_to_zip($path_to_zip, $zip, $exclusive_length);

echo "Selesai menambahkan file... Menutup arsip...<br>";
flush();

// Selesai
$zip->close();

echo "<h2>BERHASIL!</h2>";
echo "File zip telah dibuat di: <strong>" . htmlspecialchars($zip_path) . "</strong>";

?>
