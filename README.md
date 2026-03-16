# Universal File Manager for Laravel

A powerful, premium gallery-style file manager for Laravel applications using Livewire and Tailwind CSS.

## Features

- 📁 Folder management (Nested folders)
- 📤 Multiple file uploads with Drag & Drop
- 🖼️ Automatic image resizing & compression
- 🔍 Real-time search
- 🍱 Premium Gallery UI
- 📱 Responsive design

## Installation

### 1. In your Laravel Project

Since this is a local package, you need to tell your main Laravel project where to find it. Open the `composer.json` of your **main Laravel application** and add:

```json
"repositories": [
    {
        "type": "path",
        "url": "../universal-file-manager"
    }
],
```
*(Adjust the path to where your package is located)*

Then run:
```bash
composer require adeel3330/universal-file-manager:@dev
```

### 2. Publish Configuration and Assets

```bash
php artisan vendor:publish --tag=ufm-config
php artisan vendor:publish --tag=ufm-views
```

### 3. Run Migrations

```bash
php artisan migrate
```

## Usage

Once installed, you can access the file manager at:
`your-app.test/file-manager`

### Using the Livewire Component
You can also embed the file manager in any Blade view:

```blade
<livewire:ufm-file-manager />
```

## Configuration

You can customize the allowed file types, max file size, and image processing settings in `config/ufm.php`.

```php
return [
    'storage_disk' => 'public',
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'xlsx', 'zip'],
    'max_file_size' => 20480, // 20MB
    'image_processing' => [
        'enabled' => true,
        'max_width' => 1920,
        'quality' => 80,
    ],
];
```
