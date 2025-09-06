<?php
session_start();

// Configuration
$configFile = __DIR__ . '/conf/config.json';
$contentDir = __DIR__ . '/content/';
$allowedExtensions = ['html', 'svg', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'md', 'txt'];
$maxFileSize = 10 * 1024 * 1024; // 10MB

// Load configuration
function loadConfig() {
    global $configFile;
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        return $config ? $config : ['password_hash' => password_hash('admin123', PASSWORD_DEFAULT)];
    }
    // Create default config if file doesn't exist
    $defaultConfig = ['password_hash' => password_hash('admin123', PASSWORD_DEFAULT)];
    saveConfig($defaultConfig);
    return $defaultConfig;
}

// Save configuration
function saveConfig($config) {
    global $configFile;
    $confDir = dirname($configFile);
    if (!file_exists($confDir)) {
        mkdir($confDir, 0755, true);
    }
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
}

$config = loadConfig();

// Create content directory if it doesn't exist
if (!file_exists($contentDir)) {
    mkdir($contentDir, 0755, true);
}

// Handle login
if (isset($_POST['login'])) {
    $loginSuccess = false;
    
    // Check if using new hash system
    if (isset($config['password_hash'])) {
        $loginSuccess = password_verify($_POST['password'], $config['password_hash']);
    }
    // Fallback for plain text (temporary)
    elseif (isset($config['password'])) {
        if ($_POST['password'] === $config['password']) {
            $loginSuccess = true;
            // Upgrade to hash system
            $config['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            unset($config['password']);
            saveConfig($config);
        }
    }
    
    if ($loginSuccess) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle file deletion
if (isset($_POST['delete']) && isset($_SESSION['logged_in'])) {
    if (isset($_POST['filename']) && !empty($_POST['filename'])) {
        $filename = $_POST['filename'];
        
        // Security: Only allow deletion of files in content directory
        // Prevent directory traversal attacks
        $safeName = basename($filename);
        $filePath = $contentDir . $safeName;
        
        // Check if file exists and is within content directory
        if (file_exists($filePath) && strpos(realpath($filePath), realpath($contentDir)) === 0) {
            if (unlink($filePath)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'File deleted successfully!'];
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to delete file'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'File not found or access denied'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'No filename specified'];
    }
    header('Location: index.php');
    exit;
}

// Handle password change
if (isset($_POST['change_password']) && isset($_SESSION['logged_in'])) {
    if (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($currentPassword, $config['password_hash'])) {
            // Check if new passwords match
            if ($newPassword === $confirmPassword) {
                // Check password length
                if (strlen($newPassword) >= 6) {
                    // Update password in config
                    $config['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                    saveConfig($config);
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Password changed successfully!'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'New password must be at least 6 characters long'];
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'New passwords do not match'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Current password is incorrect'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'All fields are required'];
    }
    header('Location: index.php?settings=1');
    exit;
}

// Handle file upload
if (isset($_POST['upload']) && isset($_SESSION['logged_in'])) {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileTmp = $_FILES['file']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($fileExt, $allowedExtensions)) {
            if ($fileSize <= $maxFileSize) {
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                $destination = $contentDir . $safeName;
                
                // Handle duplicate files
                $counter = 1;
                $originalName = pathinfo($safeName, PATHINFO_FILENAME);
                $extension = pathinfo($safeName, PATHINFO_EXTENSION);
                
                while (file_exists($destination)) {
                    $safeName = $originalName . '_' . $counter . '.' . $extension;
                    $destination = $contentDir . $safeName;
                    $counter++;
                }
                
                if (move_uploaded_file($fileTmp, $destination)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'File uploaded successfully!'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to move uploaded file'];
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'File size exceeds limit (10MB)'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'File type not allowed'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'No file selected or upload error'];
    }
    header('Location: index.php');
    exit;
}

// Get list of files
function getFiles($dir) {
    $files = [];
    if (is_dir($dir)) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..' && is_file($dir . $item)) {
                $files[] = [
                    'name' => $item,
                    'size' => filesize($dir . $item),
                    'modified' => filemtime($dir . $item),
                    'extension' => strtolower(pathinfo($item, PATHINFO_EXTENSION))
                ];
            }
        }
    }
    // Sort by modification time (newest first)
    usort($files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    return $files;
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function getFileIcon($extension) {
    $icons = [
        'html' => '🌐',
        'svg' => '🎨',
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'webp' => '🖼️',
        'md' => '📝',
        'txt' => '📄'
    ];
    return $icons[$extension] ?? '📁';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1a1a2e">
    <title>UpBox - File Upload Tool</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/svg+xml" href="favicon-simple.svg" sizes="16x16">
    <link rel="shortcut icon" href="favicon-simple.svg">
    <link rel="apple-touch-icon" href="favicon.svg">
    
    <!-- Web App Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php if (!isset($_SESSION['logged_in'])): ?>
            <!-- Login Form -->
            <div class="login-container">
                <div class="login-box">
                    <h1>🔐 File Upload Tool</h1>
                    <p>Please enter the password to access the upload tool</p>
                    
                    <?php if (isset($error)): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="post" class="login-form">
                        <input type="password" name="password" placeholder="Enter password" required autofocus>
                        <button type="submit" name="login">Login</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Main Application -->
            <header>
                <h1>📁 File Upload Tool</h1>
                <div class="header-actions">
                    <a href="?settings=1" class="logout-btn">⚙️ Settings</a>
                    <a href="?logout" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <!-- Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="<?php echo $_SESSION['message']['type']; ?>"><?php echo htmlspecialchars($_SESSION['message']['text']); ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_GET['settings'])): ?>
            <!-- Settings Section -->
            <div class="password-change-section">
                <h2>⚙️ Settings</h2>
                
                <h3>Change Password</h3>
                <form method="post" class="password-form">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" name="current_password" id="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" name="new_password" id="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required minlength="6">
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="change-password-btn">Change Password</button>
                        <a href="index.php" class="cancel-btn">Back to Files</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <?php if (!isset($_GET['settings'])): ?>
            <!-- Upload Section -->
            <div class="upload-section">
                <h2>Upload Files</h2>
                <p class="upload-info">
                    Allowed types: HTML, SVG, Images (JPG, PNG, GIF, WebP), Markdown, Text files<br>
                    Maximum size: 10MB per file
                </p>
                
                <form method="post" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <div class="file-input-wrapper">
                        <input type="file" name="file" id="fileInput" accept=".html,.svg,.jpg,.jpeg,.png,.gif,.webp,.md,.txt;.json" required>
                        <label for="fileInput" class="file-input-label">
                            <span class="file-input-text">Choose File</span>
                            <span class="file-input-button">Browse</span>
                        </label>
                    </div>
                    <button type="submit" name="upload" class="upload-btn">Upload File</button>
                </form>
            </div>
            
            <!-- Files List -->
            <div class="files-section">
                <h2>Uploaded Files</h2>
                <?php 
                $files = getFiles($contentDir);
                if (empty($files)): 
                ?>
                    <p class="no-files">No files uploaded yet.</p>
                <?php else: ?>
                    <div class="files-grid">
                        <?php foreach ($files as $file): ?>
                            <div class="file-item">
                                <div class="file-icon"><?php echo getFileIcon($file['extension']); ?></div>
                                <div class="file-info">
                                    <a href="<?php echo 'content/' . htmlspecialchars($file['name']); ?>" 
                                       target="_blank" class="file-name">
                                        <?php echo htmlspecialchars($file['name']); ?>
                                    </a>
                                    <div class="file-meta">
                                        <span class="file-size"><?php echo formatFileSize($file['size']); ?></span>
                                        <span class="file-date"><?php echo date('M j, Y H:i', $file['modified']); ?></span>
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <form method="post" class="delete-form" style="display: inline;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($file['name'], ENT_QUOTES); ?>')">
                                        <input type="hidden" name="filename" value="<?php echo htmlspecialchars($file['name']); ?>">
                                        <button type="submit" name="delete" class="delete-btn" title="Delete file">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script src="app.js"></script>
</body>
</html>