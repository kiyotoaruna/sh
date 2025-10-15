<!DOCTYPE html>
<html>
<head>
    <title>Live Server Time</title>
</head>
<body>
    <h1>Waktu Server Saat Ini:</h1>
    <div id="liveTime" style="font-size: 3em; font-weight: bold;">Memuat...</div>

    <script>
        // Gunakan variabel global untuk melacak waktu server saat ini
        let serverTimestamp = 0;

        // Fungsi untuk mengambil waktu dari PHP
        function fetchServerTime() {
            // Menggunakan Fetch API untuk permintaan AJAX
            fetch('get_server_time.php')
                .then(response => response.json())
                .then(data => {
                    // Simpan timestamp Unix dari server
                    serverTimestamp = data.timestamp;

                    // Mulai atau lanjutkan fungsi update setiap 1 detik
                    setInterval(updateTime, 1000); 
                })
                .catch(error => {
                    console.error('Error fetching time:', error);
                    document.getElementById('liveTime').innerText = 'Gagal memuat waktu.';
                });
        }

        // Fungsi untuk mengupdate waktu setiap detik (di sisi klien)
        function updateTime() {
            // Buat objek Date dari timestamp Unix (dikalikan 1000 karena JS pakai milidetik)
            let date = new Date(serverTimestamp * 1000);
            
            // Format jam (misalnya H:i:s)
            let hours = date.getHours().toString().padStart(2, '0');
            let minutes = date.getMinutes().toString().padStart(2, '0');
            let seconds = date.getSeconds().toString().padStart(2, '0');

            // Tampilkan di elemen HTML
            document.getElementById('liveTime').innerText = `${hours}:${minutes}:${seconds}`;

            // Tambahkan 1 detik ke timestamp agar waktu terlihat bergerak (live)
            serverTimestamp += 1;
        }

        // Panggil fungsi awal saat halaman dimuat
        document.addEventListener('DOMContentLoaded', fetchServerTime);
    </script>
</body>
</html>
