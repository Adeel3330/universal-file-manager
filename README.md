# 🚀 Universal File Manager for Laravel

A state-of-the-art, premium, and feature-rich file manager designed specifically for Laravel applications. Powered by **Livewire 3**, **Alpine.js**, and **Tailwind CSS**, it offers a seamless, desktop-class experience for managing assets.

![File Manager Preview](https://via.placeholder.com/1200x600.png?text=Universal+File+Manager+Preview)

## ✨ Modern Features

### 📦 Multi-Selection & Bulk Actions
- **Intuitive Selection**: Click to select individual items or use the "Select All" feature.
- **Bulk Operations**: Copy, Move, Delete, or Download multiple files and folders in a single click.
- **Dynamic Action Menu**: A contextually aware header menu that updates based on your current selection.

### 🍱 Desktop-Class UI/UX
- **Double-Click Navigation**: Familiar interactions—double-click to enter folders or preview images.
- **Breadcrumb Navigation**: Effortlessly navigate through deep folder hierarchies.
- **Search & Filter**: Real-time searching to find files instantly as you type.
- **Custom Context Menu**: Right-click on any item for quick access to actions like Rename, Copy, Move, and Delete.

### 📤 Advanced Upload System
- **Real-Time Progress**: Interactive progress bar with per-second speed tracking.
- **ETA Calculation**: Know exactly when your uploads will finish with estimated remaining time.
- **Drag & Drop**: Simply drop files into the workspace to start an upload.
- **Processing**: Automatic image resizing and compression using Intervention Image.

### 🖼️ Image Previews & Handling
- **Built-in Modal**: Preview images without leaving the page using a sleek, glassmorphism-inspired modal.
- **Port Correction**: Intelligent URL handling that automatically detects and corrects port mismatches (e.g., port 8000 vs 8003), ensuring assets always load in local development environments.

## 🛠️ Installation

### 1. Require the Package
```bash
composer require adeel3330/universal-file-manager
```

### 2. Publish & Migrate
```bash
php artisan vendor:publish --tag=ufm-config
php artisan vendor:publish --tag=ufm-views
php artisan migrate
```

## 🚀 Usage

### Standalone Page
The file manager is accessible by default at:
`http://your-app.test/file-manager`

### Embedding in Views
You can embed the file manager into any Blade layout or panel:

```blade
<livewire:ufm-file-manager />
```

## ⚙️ Configuration

Control every aspect of the manager in `config/ufm.php`. Configure storage disks, allowed extensions, and image quality settings.

```php
return [
    'storage_disk' => 'public',
    'route_prefix' => 'file-manager',
    'middleware' => ['web'],
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'xlsx', 'zip'],
    'max_file_size' => 20480, // 20MB
    'image_processing' => [
        'enabled' => true,
        'max_width' => 1920,
        'quality' => 80,
    ],
];
```

## 🎨 Technology Stack
- **Framework**: Laravel 12+
- **Interactivity**: Livewire 4 & Alpine.js
- **Styling**: Tailwind CSS
- **Image Handling**: Intervention Image
- **Storage**: Laravel Storage abstraction

---
Developed with ❤️ by the Xiaroo Team.
