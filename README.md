# UpBox - File Upload Tool

A beautiful, secure, and modern file upload tool with password protection and dark mode interface. Perfect for quickly sharing HTML files, SVG graphics, images, and documents.

![UpBox Interface](https://img.shields.io/badge/Interface-Dark%20Mode-blue) ![PHP](https://img.shields.io/badge/PHP-7.4+-green) ![License](https://img.shields.io/badge/License-MIT-yellow)

## ✨ Features

### 🔐 Security
- **Password Protection** - Simple session-based authentication
- **File Validation** - Strict file type and size checking
- **Secure Upload** - Sanitized file names and safe storage

### 📁 File Support
- **Web Files**: HTML, SVG
- **Images**: JPG, JPEG, PNG, GIF, WebP
- **Documents**: Markdown (.md), Text files (.txt)
- **Size Limit**: 10MB per file

### 🎨 Modern Interface
- **Dark Mode Design** - Beautiful glassmorphism effects
- **Responsive Layout** - Works on desktop, tablet, and mobile
- **Drag & Drop** - Intuitive file uploading
- **Live Preview** - Image preview before upload
- **Smooth Animations** - Professional UI transitions

### 🚀 Advanced Features
- **File Search** - Filter uploaded files instantly
- **Copy URLs** - One-click file link copying
- **Duplicate Handling** - Automatic file renaming
- **File Management** - View file sizes, dates, and types
- **Keyboard Shortcuts** - Ctrl+U to upload, Escape to clear

## 📦 Installation

### Requirements
- PHP 7.4 or higher
- Web server (Apache, Nginx, etc.)
- Write permissions for file uploads

### Quick Setup

1. **Download the files**
   ```bash
   git clone https://github.com/yourusername/upbox.git
   cd upbox
   ```

2. **Upload to your web server**
   ```
   upbox/
   ├── index.php
   ├── style.css
   ├── app.js
   └── content/ (created automatically)
   ```

3. **Set permissions** (Linux/Mac)
   ```bash
   chmod 755 .
   chmod 777 content  # or create manually with write permissions
   ```

4. **Configure password** (Edit `index.php` line 6)
   ```php
   $password = 'your-secure-password'; // Change this!
   ```

5. **Access your installation**
   - Navigate to `http://yourserver.com/upbox/`
   - Enter your password
   - Start uploading files!

## 🔧 Configuration

### Password Setup
Edit the password in `index.php`:
```php
$password = 'admin123'; // Change this to your secure password
```

### File Settings
Customize allowed file types and size limits:
```php
$allowedExtensions = ['html', 'svg', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'md', 'txt'];
$maxFileSize = 10 * 1024 * 1024; // 10MB
```

### PHP Configuration
Ensure your `php.ini` has these settings:
```ini
file_uploads = On
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 30
```

## 🎯 Usage

### Basic Upload
1. **Login** with your password
2. **Choose a file** or drag & drop
3. **Click Upload** - files are stored in `/content/`
4. **Access files** via the generated links

### Keyboard Shortcuts
- `Ctrl + U` - Open file picker
- `Escape` - Clear file selection
- `Enter` - Submit upload (when file selected)

### File Management
- **View All Files** - Automatically listed with details
- **Search Files** - Use the search bar to filter
- **Copy URLs** - Click the 📋 button next to any file
- **Direct Access** - Files accessible at `/content/filename.ext`

## 🛠️ Troubleshooting

### Upload Not Working?

1. **Check PHP Settings**
   ```php
   // Add to top of index.php for debugging
   echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
   echo "File Uploads Enabled: " . (ini_get('file_uploads') ? 'Yes' : 'No') . "<br>";
   ```

2. **Verify Permissions**
   ```bash
   ls -la content/  # Should show write permissions
   ```

3. **Enable Error Reporting**
   ```php
   // Add to top of index.php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### Common Issues

| Problem | Solution |
|---------|----------|
| "Permission denied" | `chmod 777 content/` |
| "File too large" | Increase `upload_max_filesize` in php.ini |
| "No files showing" | Check if `/content/` directory exists |
| "Login not working" | Verify session support in PHP |

## 🎨 Customization

### Styling
Edit `style.css` to customize:
- **Colors**: Change the gradient backgrounds
- **Layout**: Modify grid and spacing
- **Animations**: Adjust transition effects

### Functionality  
Modify `app.js` for:
- **File validation**: Add new file types
- **UI behavior**: Custom interactions
- **Features**: Additional functionality

### PHP Backend
Update `index.php` for:
- **Security**: Enhanced authentication
- **File handling**: Custom processing
- **Database**: Store file metadata

## 🔒 Security Notes

- **Change the default password** immediately
- **Use HTTPS** in production environments
- **Regular backups** of uploaded files
- **Monitor disk usage** - no automatic cleanup
- **Consider file scanning** for malware in production

## 📄 File Structure

```
upbox/
├── index.php          # Main application & upload handler
├── style.css          # Dark mode styling & animations  
├── app.js             # Client-side functionality
├── content/           # Uploaded files directory
├── README.md          # This documentation
└── .htaccess          # Optional Apache configuration
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📜 License

This project is open source and available under the [MIT License](LICENSE).



---

**Made with ❤️ for easy file sharing**

*Star ⭐ this repo if you find it useful!*