<?php
session_start();

// Configuration
$password = 'admin123'; // Change this password
$contentDir = 'content/';
$allowedExtensions = ['html', 'svg', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'md', 'txt'];
$maxFileSize = 10 * 1024 * 1024; // 10MB

// Create content directory if it doesn't exist
if (!file_exists($contentDir)) {
    mkdir($contentDir, 0755, true);
}

// Handle login
if (isset($_POST['login'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
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

// Handle file upload
if (isset($_POST['upload']) && isset($_SESSION['logged_in'])) {
    $uploadSuccess = false;
    $uploadError = '';
    
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
                    $uploadSuccess = true;
                } else {
                    $uploadError = 'Failed to move uploaded file';
                }
            } else {
                $uploadError = 'File size exceeds limit (10MB)';
            }
        } else {
            $uploadError = 'File type not allowed';
        }
    } else {
        $uploadError = 'No file selected or upload error';
    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Tool</title>
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
                <a href="?logout" class="logout-btn">Logout</a>
            </header>
            
            <!-- Upload Section -->
            <div class="upload-section">
                <h2>Upload Files</h2>
                <p class="upload-info">
                    Allowed types: HTML, SVG, Images (JPG, PNG, GIF, WebP), Markdown, Text files<br>
                    Maximum size: 10MB per file
                </p>
                
                <?php if (isset($uploadSuccess) && $uploadSuccess): ?>
                    <div class="success">File uploaded successfully!</div>
                <?php endif; ?>
                
                <?php if (isset($uploadError) && $uploadError): ?>
                    <div class="error"><?php echo htmlspecialchars($uploadError); ?></div>
                <?php endif; ?>
                
                <form method="post" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <div class="file-input-wrapper">
                        <input type="file" name="file" id="fileInput" accept=".html,.svg,.jpg,.jpeg,.png,.gif,.webp,.md,.txt" required>
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
                                    <a href="<?php echo $contentDir . htmlspecialchars($file['name']); ?>" 
                                       target="_blank" class="file-name">
                                        <?php echo htmlspecialchars($file['name']); ?>
                                    </a>
                                    <div class="file-meta">
                                        <span class="file-size"><?php echo formatFileSize($file['size']); ?></span>
                                        <span class="file-date"><?php echo date('M j, Y H:i', $file['modified']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="app.js"></script>
</body>
</html>