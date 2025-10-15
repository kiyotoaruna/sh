<?php
// Tentukan perintah CMD yang ingin Anda jalankan
// Contoh: time /T (untuk menampilkan waktu)
$command_time = 'time /T';

// Contoh: date /T (untuk menampilkan tanggal)
$command_date = 'date /T';

// Contoh: systeminfo | findstr /B /C:"OS Name" (untuk menampilkan nama OS)
$command_os = 'systeminfo | findstr /B /C:"OS Name"'; 

// 1. Menggunakan shell_exec(): Mengembalikan seluruh output sebagai string.
$server_time = shell_exec($command_time);
$server_date = shell_exec($command_date);
$os_name = shell_exec($command_os);

// Mengatur tampilan halaman
echo "<h1>Hasil Perintah CMD di Server Windows</h1>";

// Tampilkan output
echo "<h2>Waktu Server (Perintah: `time /T`)</h2>";
// Gunakan trim() untuk menghilangkan spasi atau baris baru yang tidak perlu
echo "<pre>" . trim($server_time) . "</pre>"; 
// Output akan sesuai dengan format waktu CMD di server Anda (misalnya 14:06:45, tanpa detik)

echo "<h2>Tanggal Server (Perintah: `date /T`)</h2>";
echo "<pre>" . trim($server_date) . "</pre>";

echo "<h2>Nama Sistem Operasi (Perintah: `systeminfo | findstr /B /C:\"OS Name\"`)</h2>";
echo "<pre>" . trim($os_name) . "</pre>";

// 2. Opsi: Menggunakan exec() untuk perintah yang lebih kompleks
// exec() mengembalikan baris terakhir output, tetapi mengisi array dengan semua baris.
/*
$output_array = [];
$exit_code = 0;
// Perintah: ipconfig (menampilkan informasi jaringan)
$last_line = exec('ipconfig', $output_array, $exit_code);

echo "<h2>Informasi Jaringan (Perintah: `ipconfig`)</h2>";
echo "<pre>" . implode("\n", $output_array) . "</pre>";
echo "<p>Exit Code: " . $exit_code . "</p>"; // Exit Code 0 berarti berhasil
*/
?>
